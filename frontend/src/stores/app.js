import { defineStore } from "pinia";

export const useAppStore = defineStore('app', {
    state: () => ({
        showTaskDialog: false,
        accesstoken: ''
    }),
    getters: {
    },
    actions: {
        reset () {
            this.accesstoken = '',
            this.showTaskDialog = false;
        },
        toggleTaskDialog() {
            this.showTaskDialog = !this.showTaskDialog
        }
    }
})