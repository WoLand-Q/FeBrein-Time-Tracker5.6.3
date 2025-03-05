<template>
    <div class="timer-container animate__animated animate__fadeIn">
        <h2 class="timer-title">Work Timer</h2>
        <div class="timer-display">
            <span>{{ formattedTime }}</span>
        </div>
        <div class="timer-controls">
            <button
                class="btn btn-start"
                v-if="!isRunning"
                @click="startTimer"
            >
                Start
            </button>
            <button
                class="btn btn-pause"
                v-if="isRunning"
                @click="pauseTimer"
            >
                Pause
            </button>
            <button
                class="btn btn-reset"
                @click="resetTimer"
            >
                Reset
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: "Timer",
    data() {
        return {
            time: 0,
            timer: null,
            isRunning: false,
        };
    },
    computed: {
        formattedTime() {
            const minutes = Math.floor(this.time / 60)
                .toString()
                .padStart(2, "0");
            const seconds = (this.time % 60).toString().padStart(2, "0");
            return `${minutes}:${seconds}`;
        },
    },
    methods: {
        startTimer() {
            if (!this.isRunning) {
                this.timer = setInterval(() => {
                    this.time++;
                }, 1000);
                this.isRunning = true;
            }
        },
        pauseTimer() {
            clearInterval(this.timer);
            this.isRunning = false;
        },
        resetTimer() {
            clearInterval(this.timer);
            this.time = 0;
            this.isRunning = false;
        },
    },
};
</script>

<style scoped>
.timer-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #1f1f1f;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    color: #fff;
}

.timer-title {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: #ff4545;
}

.timer-display {
    font-size: 3rem;
    font-weight: bold;
    background: #2d2d2d;
    padding: 1rem 2rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    color: #fff;
}

.timer-controls {
    display: flex;
    gap: 1rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: transform 0.2s, background-color 0.3s;
}

.btn:hover {
    transform: scale(1.03);
}

.btn-start {
    background-color: #4caf50;
    color: #fff;
}

.btn-pause {
    background-color: #ff9800;
    color: #fff;
}

.btn-reset {
    background-color: #f44336;
    color: #fff;
}
</style>
