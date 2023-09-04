import { defineStore } from "pinia";
import { useTasksStore } from "./tasks";

export const useTaskStore = defineStore('task', {
    state: () => ({
        task: {

        }
    }),
    getters: {
        // computed methods
    },
    actions: {
        addTask() {
            const tasksStore = useTasksStore();
            window.axios.post('/api/task', {...this.task,
                status: 0,
                priority: 1,
                is_archived: false
            })
            .then(()=> tasksStore.getTasks())
            .catch((errors) => {
                console.log(errors);
                if (errors) {}
            });
        }

    }
})