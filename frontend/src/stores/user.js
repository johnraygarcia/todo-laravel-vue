import { defineStore } from "pinia";

export const useUserStore = defineStore('user', {
    state: () => ({
        user: {}
    }),
    getters: {
        getUser () {
            return this.user
        }
    },
    actions: {
        async getCurrentUser(){
            this.isLoading = true;
            await window.axios.get('/api/user').then(result => {
                this.user = result.data.user
            })
        },
        async resetCurrentUser(){
            this.isLoading = true;
            this.user = {}
        },
    }
})