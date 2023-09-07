import { defineStore } from "pinia";

export const useTaskFiltersStore = defineStore('taskFilters', {
    state: () => ({
        filter: {
            name: null,
            priority: null,
            due_date: null
        }
    }),
})