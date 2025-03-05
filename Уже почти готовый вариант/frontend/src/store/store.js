// store.js
import { createStore } from "vuex";

export default createStore({
    state: {
        user: {
            role: null,
            isAuthenticated: false,
        },
    },
    mutations: {
        setUser(state, payload) {
            // payload: { role: "admin" или "user", isAuthenticated: true }
            state.user.role = payload.role;
            state.user.isAuthenticated = payload.isAuthenticated;
        },
        logout(state) {
            state.user.role = null;
            state.user.isAuthenticated = false;
        },
    },
});
