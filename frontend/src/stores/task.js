import { defineStore } from "pinia";
import { useTasksStore } from "./tasks";
import { useAppStore } from "./app";

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
            const appStore = useAppStore();
            window.axios.post('/api/task', {...this.task,
                status: 0,
                priority: 1,
                is_archived: false
            })
            .then(()=> {
                tasksStore.getTasks()
                appStore.showTaskDialog = false;
            })
            .catch((errors) => {
                console.log(errors);
                if (errors) {}
            });
        }

    }
})