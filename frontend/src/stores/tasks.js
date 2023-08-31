import axios from "axios";
import { defineStore } from "pinia";

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        isLoading: false
    }),
    getters: {

    },
    actions: {
        async getTasks(){
            this.isLoading = true;
            await axios.get('/api/task').then(result => {
                this.tasks = result.data.data.map((task) => {
                    let prioLabel = 'low';
                    switch (task.priority) {
                        case 1:
                            prioLabel = 'urgent'
                            break;
                        case 2:
                            prioLabel = 'high'
                            break;
                        case 3:
                            prioLabel = 'normal'
                            break;
                        case 4:
                            prioLabel = 'low'
                            break;
                    }
                    const pl = {
                        key: prioLabel,
                        value: task.priority
                    }
                    return { ...task, priorityLevel: pl}
                });
                this.isLoading = false;
            })
        }
    }
})