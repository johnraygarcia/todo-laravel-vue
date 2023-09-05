import { defineStore } from "pinia";

export const useAppStore = defineStore('app', {
    state: () => ({
        showTaskDialog: false,
    }),
    getters: {
    },
    actions: {
        reset () {
            this.showTaskDialog = false;
        },
        toggleTaskDialog() {
            this.showTaskDialog = !this.showTaskDialog
        }
    }
})