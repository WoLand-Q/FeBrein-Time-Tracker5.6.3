<template>
  <div class="timer-page">
    <h1 class="page-title">Таймер користувача</h1>

    <!-- Компонент таймера -->
    <Timer
        :currentStatus="myStatus"
        :startTime="myStartTime"
        :dayClosed="myDayClosed"
        @startWork="startWork"
        @startBreak="startBreak"
        @stopBreak="stopBreak"
        @stopWork="stopWork"
    />

    <!-- Блок с таблицей статусов коллег -->
    <div class="colleagues-block">
      <h2>Статус колег</h2>
      <StatusTable :users="colleagues" />
    </div>
  </div>
</template>

<script>
import Timer from "@/components/Timer.vue";
import StatusTable from "@/components/StatusTable.vue";
import api from "@/api";

export default {
  name: "TimerPage",
  components: { Timer, StatusTable },
  data() {
    return {
      currentUserId: null,

      // Собственные статус/время
      myStatus: "завершено",  // "працює" | "перерва" | "завершено"
      myStartTime: null,
      myDayClosed: false,     // если true — нельзя снова начинать

      colleagues: [],         // список коллег в таблице
      pollingInterval: null,  // интервал для опроса (polling)
    };
  },
  methods: {
    async mountedActions() {
      await this.fetchCurrentUser();
      await this.fetchStatuses();
      // Периодический опрос каждые 10 секунд (можно изменить)
      this.pollingInterval = setInterval(() => {
        this.fetchStatuses();
      }, 10000);
    },

    // Узнаём ID текущего пользователя
    async fetchCurrentUser() {
      try {
        const resp = await api.get("/api/user");
        this.currentUserId = resp.data.id;
      } catch (err) {
        console.error("Ошибка fetchCurrentUser:", err);
        this.currentUserId = null;
      }
    },

    // Загружаем логи, пользователей, вычисляем статусы
    async fetchStatuses() {
      try {
        // 1) Логи
        const respLogs = await api.get("/api/userTimeLogs");
        const timeLogs = respLogs.data.data || [];

        // 2) Список пользователей
        const respUsers = await api.get("/api/users");
        const allUsers = respUsers.data.data || respUsers.data || [];

        // Подготавливаем заготовки для каждого пользователя
        const userMap = allUsers.map(u => ({
          id: u.id,
          name: u.name || u.login || ("User #" + u.id),
          status: "завершено",
          workedMins: 0,
          breakMins: 0,
          startTime: null,
          dayClosed: false,

          // новые поля (последние времена)
          lastStartWork: null,
          lastStopWork: null,
          lastStartBreak: null,
          lastStopBreak: null,
        }));

        // Группируем логи по user_id
        const logsByUser = {};
        for (const log of timeLogs) {
          const uid = log.user_id;
          if (!logsByUser[uid]) logsByUser[uid] = [];
          logsByUser[uid].push(log);
        }

        // Обрабатываем логи каждого пользователя
        for (const uid of Object.keys(logsByUser)) {
          const userLogs = logsByUser[uid].slice().sort((a, b) =>
              new Date(a.acted_at) - new Date(b.acted_at)
          );
          const parsed = this.parseUserLogsForToday(userLogs);

          // Находим в userMap нужного пользователя
          const userObj = userMap.find(u => u.id === Number(uid));
          if (userObj) {
            userObj.status      = parsed.status;
            userObj.workedMins  = parsed.totalWorkMins;
            userObj.breakMins   = parsed.totalBreakMins;
            userObj.startTime   = parsed.startTime;
            userObj.dayClosed   = parsed.dayClosed;

            userObj.lastStartWork   = parsed.lastStartWork;
            userObj.lastStopWork    = parsed.lastStopWork;
            userObj.lastStartBreak  = parsed.lastStartBreak;
            userObj.lastStopBreak   = parsed.lastStopBreak;
          }
        }

        // Заполняем таблицу
        this.colleagues = userMap;

        // Данные о себе
        if (this.currentUserId) {
          const meObj = userMap.find(u => u.id === this.currentUserId);
          if (meObj) {
            this.myStatus    = meObj.status;
            this.myStartTime = meObj.startTime;
            this.myDayClosed = meObj.dayClosed;
          } else {
            this.myStatus    = "завершено";
            this.myStartTime = null;
            this.myDayClosed = false;
          }
        }
      } catch (err) {
        console.error("Ошибка fetchStatuses:", err);
      }
    },

    /**
     * Парсим логи только за сегодня.
     * Возвращаем:
     * {
     *   status: "працює"|"перерва"|"завершено",
     *   totalWorkMins: number,
     *   totalBreakMins: number,
     *   startTime: string | null,
     *   dayClosed: boolean, // если true, пользователь уже сделал stopWork (event_id=4) последним
     *
     *   lastStartWork:  string | null,
     *   lastStopWork:   string | null,
     *   lastStartBreak: string | null,
     *   lastStopBreak:  string | null
     * }
     */
    parseUserLogsForToday(userLogs) {
      let totalWorkSeconds  = 0;
      let totalBreakSeconds = 0;

      let currentState   = "none";  // "work", "break" или "none"
      let lastTime       = null;
      let lastEventStart = null;

      // Новые поля
      let lastStartWork   = null;
      let lastStopWork    = null;
      let lastStartBreak  = null;
      let lastStopBreak   = null;

      let dayClosed = false; // Если в конце дня event_id=4

      // Фильтруем только сегодняшние логи
      const todayStr = new Date().toISOString().slice(0, 10);
      userLogs = userLogs.filter(l => l.acted_at.slice(0,10) === todayStr);

      if (userLogs.length === 0) {
        return {
          status: "завершено",
          totalWorkMins: 0,
          totalBreakMins: 0,
          startTime: null,
          dayClosed: false,
          lastStartWork: null,
          lastStopWork: null,
          lastStartBreak: null,
          lastStopBreak: null,
        };
      }

      for (const log of userLogs) {
        const eventId = log.event_id; // 1=start,2=start_break,3=stop_break,4=stop
        const t       = new Date(log.acted_at).getTime() / 1000;

        // Перед сменой состояния считаем накопленное время
        if (currentState === "work" && (eventId !== 1)) {
          // если мы были в work и пришло любое другое событие
          totalWorkSeconds += (t - (lastTime ?? t));
        }
        else if (currentState === "break" && (eventId !== 2 && eventId !== 3)) {
          // если мы были в break и пришло событие startWork/stopWork
          totalBreakSeconds += (t - (lastTime ?? t));
        }
        else if (currentState === "break" && eventId === 3) {
          // stop break => перед этим заканчиваем break
          totalBreakSeconds += (t - (lastTime ?? t));
        }

        // Обновляем состояние
        if (eventId === 1) {
          // start work
          currentState    = "work";
          lastTime        = t;
          lastEventStart  = log.acted_at;
          lastStartWork   = log.acted_at;
          dayClosed       = false;  // раз начали заново — явно не закрыт
        }
        else if (eventId === 2) {
          // start break
          currentState    = "break";
          lastTime        = t;
          lastEventStart  = log.acted_at;
          lastStartBreak  = log.acted_at;
        }
        else if (eventId === 3) {
          // stop break => back to work
          currentState    = "work";
          lastTime        = t;
          lastEventStart  = log.acted_at;
          lastStopBreak   = log.acted_at;
        }
        else if (eventId === 4) {
          // stop work
          if (currentState === "work") {
            totalWorkSeconds += (t - (lastTime ?? t));
          }
          else if (currentState === "break") {
            totalBreakSeconds += (t - (lastTime ?? t));
          }
          currentState    = "none";
          lastTime        = t;
          lastEventStart  = null;
          lastStopWork    = log.acted_at;
          dayClosed       = true; // если пользователь сделал stopWork
        }
      }

      // Если остались незавершённые состояния до текущего момента
      const nowSec = Date.now() / 1000;
      if (currentState === "work" && lastTime != null) {
        totalWorkSeconds += (nowSec - lastTime);
      }
      else if (currentState === "break" && lastTime != null) {
        totalBreakSeconds += (nowSec - lastTime);
      }

      // Итоговый статус
      let finalStatus = "завершено";
      if (currentState === "work")   finalStatus = "працює";
      if (currentState === "break") finalStatus = "перерва";

      return {
        status: finalStatus,
        totalWorkMins:  Math.floor(totalWorkSeconds / 60),
        totalBreakMins: Math.floor(totalBreakSeconds / 60),
        startTime: (finalStatus === "працює" || finalStatus === "перерва")
            ? lastEventStart
            : null,

        dayClosed,

        lastStartWork,
        lastStopWork,
        lastStartBreak,
        lastStopBreak,
      };
    },

    // Методы, вызываемые из <Timer />
    async startWork() {
      try {
        // Отправляем только event_id, сервер сам подставляет время
        await api.post("/api/userTimeLogs", { event_id: 1 });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка startWork:", err);
      }
    },
    async startBreak() {
      try {
        await api.post("/api/userTimeLogs", { event_id: 2 });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка startBreak:", err);
      }
    },
    async stopBreak() {
      try {
        await api.post("/api/userTimeLogs", { event_id: 3 });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка stopBreak:", err);
      }
    },
    async stopWork() {
      try {
        await api.post("/api/userTimeLogs", { event_id: 4 });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка stopWork:", err);
      }
    },
  },

  mounted() {
    this.mountedActions();
  },

  beforeDestroy() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
  },
};
</script>

<style scoped>
.timer-page {
  padding: 1rem;
  color: #fff;
  background: #121212;
  min-height: 100vh; /* чтобы фон тянулся */
}

.page-title {
  font-size: 1.8rem;
  margin-bottom: 1rem;
  color: #ff4545;
}

.colleagues-block {
  margin-top: 2rem;
  background: #1f1f1f;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
}

.colleagues-block h2 {
  margin: 0 0 1rem;
  color: #ff4545;
}
</style>
