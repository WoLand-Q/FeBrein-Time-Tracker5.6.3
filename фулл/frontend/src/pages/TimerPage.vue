<template>
  <div class="p-4">
    <h1 class="text-3xl font-bold mb-6">Таймер користувача</h1>

    <!-- Компонент таймера -->
    <Timer
        :current-status="myStatus"
        @startWork="startWork"
        @startBreak="startBreak"
        @stopBreak="stopBreak"
        @stopWork="stopWork"
    />

    <div class="mt-8">
      <h2 class="text-2xl font-semibold mb-4">Статус колег</h2>
      <StatusTable :users="colleagues" />
    </div>
  </div>
</template>

<script>
import Timer from "@/components/Timer.vue";
import StatusTable from "@/components/StatusTable.vue";
import api from "@/api"; // Ваш axios-клиент

export default {
  name: "TimerPage",
  components: {
    Timer,
    StatusTable,
  },
  data() {
    return {
      currentUserId: null,
      myStatus: "завантаження", // "працює", "перерва", "завершено"
      colleagues: [],
    };
  },
  methods: {
    async fetchCurrentUser() {
      try {
        const resp = await api.get("/api/user");
        this.currentUserId = resp.data.id;
      } catch (e) {
        console.error("Ошибка fetchCurrentUser:", e);
        this.currentUserId = null;
      }
    },

    async fetchStatuses() {
      try {
        const respLogs = await api.get("/api/admin/userTimeLogs");
        const timeLogs = respLogs.data.data;

        const respUsers = await api.get("/api/admin/users");
        const allUsers = respUsers.data.data || respUsers.data;

        // По умолчанию
        const userMap = allUsers.map(u => ({
          id: u.id,
          name: u.name || u.login,
          currentStatus: "завершено",
          workedMins: 0,
          breakMins: 0,
        }));

        // Группируем
        const logsByUser = {};
        for (const log of timeLogs) {
          const uid = log.user_id;
          if (!logsByUser[uid]) logsByUser[uid] = [];
          logsByUser[uid].push(log);
        }

        const todayStr = new Date().toISOString().slice(0, 10);

        for (const uid of Object.keys(logsByUser)) {
          let userLogs = logsByUser[uid];
          userLogs = userLogs.filter(l => l.acted_at.slice(0,10) === todayStr);
          if (userLogs.length === 0) continue;

          userLogs.sort((a,b) => new Date(a.acted_at) - new Date(b.acted_at));

          const { status, totalWorkMins, totalBreakMins } = this.parseUserLogsForToday(userLogs);

          const userObj = userMap.find(u => u.id === parseInt(uid));
          if (userObj) {
            userObj.currentStatus = status;
            userObj.workedMins = totalWorkMins;
            userObj.breakMins = totalBreakMins;
          }
        }

        this.colleagues = userMap;

        if (this.currentUserId) {
          const meObj = userMap.find(u => u.id === this.currentUserId);
          this.myStatus = meObj ? meObj.currentStatus : "завершено";
        } else {
          this.myStatus = "завершено";
        }
      } catch (err) {
        console.error("Ошибка fetchStatuses:", err);
      }
    },

    parseUserLogsForToday(userLogs) {
      let totalWorkSeconds = 0;
      let totalBreakSeconds = 0;

      let currentState = "none";
      let lastTime = null;

      for (const log of userLogs) {
        const eventId = log.event_id;
        const t = new Date(log.acted_at).getTime() / 1000;

        if (eventId === 1) {
          // start work
          if (currentState === "work" && lastTime != null) {
            totalWorkSeconds += (t - lastTime);
          }
          if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState = "work";
          lastTime = t;
        } else if (eventId === 2) {
          // start_break
          if (currentState === "work" && lastTime != null) {
            totalWorkSeconds += (t - lastTime);
          }
          if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState = "break";
          lastTime = t;
        } else if (eventId === 3) {
          // stop_break
          if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState = "work";
          lastTime = t;
        } else if (eventId === 4) {
          // stop
          if (currentState === "work" && lastTime != null) {
            totalWorkSeconds += (t - lastTime);
          } else if (currentState === "break" && lastTime != null) {
            totalBreakSeconds += (t - lastTime);
          }
          currentState = "none";
          lastTime = t;
        }
      }

      // Если в конце ещё работаем / на перерыве — добавим до "сейчас"
      const nowSec = Date.now() / 1000;
      if (currentState === "work" && lastTime != null) {
        totalWorkSeconds += (nowSec - lastTime);
      } else if (currentState === "break" && lastTime != null) {
        totalBreakSeconds += (nowSec - lastTime);
      }

      let finalStatus = "завершено";
      if (currentState === "work") finalStatus = "працює";
      if (currentState === "break") finalStatus = "перерва";

      return {
        status: finalStatus,
        totalWorkMins: Math.floor(totalWorkSeconds / 60),
        totalBreakMins: Math.floor(totalBreakSeconds / 60),
      };
    },

    // Методы, вызываемые из <Timer />, принимаем actedAt
    async startWork(actedAt) {
      try {
        await api.post("/api/admin/userTimeLogs", {
          event_id: 1,
          acted_at: actedAt,
        });
        this.myStatus = "працює";
        this.fetchStatuses();
      } catch (error) {
        console.error("Помилка startWork:", error);
      }
    },
    async startBreak(actedAt) {
      try {
        await api.post("/api/admin/userTimeLogs", {
          event_id: 2,
          acted_at: actedAt,
        });
        this.myStatus = "перерва";
        this.fetchStatuses();
      } catch (error) {
        console.error("Помилка startBreak:", error);
      }
    },
    async stopBreak(actedAt) {
      try {
        await api.post("/api/admin/userTimeLogs", {
          event_id: 3,
          acted_at: actedAt,
        });
        this.myStatus = "працює";
        this.fetchStatuses();
      } catch (error) {
        console.error("Помилка stopBreak:", error);
      }
    },
    async stopWork(actedAt) {
      try {
        await api.post("/api/admin/userTimeLogs", {
          event_id: 4,
          acted_at: actedAt,
        });
        this.myStatus = "завершено";
        this.fetchStatuses();
      } catch (error) {
        console.error("Помилка stopWork:", error);
      }
    },
  },
  async mounted() {
    await this.fetchCurrentUser();
    await this.fetchStatuses();
  },
};
</script>

<style scoped>
/* Ваши стили */
</style>
