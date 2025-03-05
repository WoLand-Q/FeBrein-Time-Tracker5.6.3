<template>
  <div class="timer-page">
    <h1 class="page-title">Таймер користувача</h1>

    <!-- Компонент таймера -->
    <Timer
        :currentStatus="myStatus"
        :startTime="myStartTime"
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
      myStatus: "завершено", // "працює" | "перерва" | "завершено"
      myStartTime: null,     // когда пользователь начал активное состояние (работа/перерыв)
      colleagues: [],
    };
  },
  methods: {
    async mountedActions() {
      await this.fetchCurrentUser();
      await this.fetchStatuses();
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

        // Шаблон под каждого пользователя
        const userMap = allUsers.map(u => ({
          id: u.id,
          name: u.name || u.login || ("User #" + u.id),
          currentStatus: "завершено",
          workedMins: 0,
          breakMins: 0,
          startTime: null, // время начала текущего состояния
        }));

        // Группируем логи по user_id
        const logsByUser = {};
        for (const log of timeLogs) {
          const uid = log.user_id;
          if (!logsByUser[uid]) logsByUser[uid] = [];
          logsByUser[uid].push(log);
        }

        // Парсим логи
        for (const uid of Object.keys(logsByUser)) {
          const userLogs = logsByUser[uid].slice().sort((a, b) =>
              new Date(a.acted_at) - new Date(b.acted_at)
          );
          const parsed = this.parseUserLogsForToday(userLogs);

          // Находим в userMap нужного пользователя
          const userObj = userMap.find(u => u.id === Number(uid));
          if (userObj) {
            userObj.currentStatus = parsed.status;
            userObj.workedMins    = parsed.totalWorkMins;
            userObj.breakMins     = parsed.totalBreakMins;
            userObj.startTime     = parsed.startTime; // время начала (работы или перерыва)
          }
        }

        this.colleagues = userMap;

        // Данные о себе
        if (this.currentUserId) {
          const meObj = userMap.find(u => u.id === this.currentUserId);
          if (meObj) {
            this.myStatus    = meObj.currentStatus;
            this.myStartTime = meObj.startTime;
          } else {
            this.myStatus    = "завершено";
            this.myStartTime = null;
          }
        }
      } catch (err) {
        console.error("Ошибка fetchStatuses:", err);
      }
    },

    /**
     * Парсим логи только за сегодня (пример).
     * Возвращаем:
     *  {
     *    status: "працює"|"перерва"|"завершено",
     *    totalWorkMins: number,
     *    totalBreakMins: number,
     *    startTime: string | null  // когда пользователь вошёл в текущее состояние
     *  }
     */
    parseUserLogsForToday(userLogs) {
      let totalWorkSeconds  = 0;
      let totalBreakSeconds = 0;
      let currentState = "none";
      let lastTime     = null;

      // Доп. переменная, чтобы отслеживать, когда пользователь
      // **в последний раз** перешёл в «work» или «break».
      let lastEventStart = null;

      // Отфильтруем логи только за сегодня
      const todayStr = new Date().toISOString().slice(0, 10);
      userLogs = userLogs.filter(l => l.acted_at.slice(0,10) === todayStr);
      if (userLogs.length === 0) {
        return {
          status: "завершено",
          totalWorkMins: 0,
          totalBreakMins: 0,
          startTime: null,
        };
      }

      for (const log of userLogs) {
        const eventId = log.event_id;
        const t = new Date(log.acted_at).getTime() / 1000;

        if (eventId === 1) {
          // start work
          if (currentState === "work" && lastTime != null) {
            totalWorkSeconds += (t - lastTime);
          } else if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState    = "work";
          lastTime        = t;
          lastEventStart  = log.acted_at; // когда перешли в "працює"
        }
        else if (eventId === 2) {
          // start break
          if (currentState === "work" && lastTime != null) {
            totalWorkSeconds += (t - lastTime);
          } else if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState    = "break";
          lastTime        = t;
          lastEventStart  = log.acted_at; // когда перешли в "перерва"
        }
        else if (eventId === 3) {
          // stop break => back to work
          if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState    = "work";
          lastTime        = t;
          lastEventStart  = log.acted_at; // снова "працює"
        }
        else if (eventId === 4) {
          // stop work
          if (currentState === "work" && lastTime != null) {
            totalWorkSeconds += (t - lastTime);
          } else if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState    = "none";
          lastTime        = t;
          lastEventStart  = null;
        }
      }

      // Если остались незавершённые состояния
      const nowSec = Date.now() / 1000;
      if (currentState === "work" && lastTime != null) {
        totalWorkSeconds += (nowSec - lastTime);
      } else if (currentState === "break" && lastTime != null) {
        totalBreakSeconds += (nowSec - lastTime);
      }

      let finalStatus = "завершено";
      if (currentState === "work")   finalStatus = "працює";
      if (currentState === "break") finalStatus = "перерва";

      return {
        status: finalStatus,
        totalWorkMins:  Math.floor(totalWorkSeconds / 60),
        totalBreakMins: Math.floor(totalBreakSeconds / 60),
        // Если статус активный, то lastEventStart хранит время входа в это состояние
        startTime: (finalStatus === "працює" || finalStatus === "перерва")
            ? lastEventStart
            : null
      };
    },

    // Методы, вызываемые из <Timer />
    async startWork(dateTime) {
      try {
        await api.post("/api/userTimeLogs", { event_id: 1, acted_at: dateTime });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка startWork:", err);
      }
    },
    async startBreak(dateTime) {
      try {
        await api.post("/api/userTimeLogs", { event_id: 2, acted_at: dateTime });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка startBreak:", err);
      }
    },
    async stopBreak(dateTime) {
      try {
        await api.post("/api/userTimeLogs", { event_id: 3, acted_at: dateTime });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка stopBreak:", err);
      }
    },
    async stopWork(dateTime) {
      try {
        await api.post("/api/userTimeLogs", { event_id: 4, acted_at: dateTime });
        this.fetchStatuses();
      } catch (err) {
        console.error("Ошибка stopWork:", err);
      }
    },
  },
  async mounted() {
    await this.mountedActions();
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
