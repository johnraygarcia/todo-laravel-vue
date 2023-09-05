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
            if (this.task.id == null ) {

                window.axios.post('/api/task', {...this.task,
                    status: 0,
                    is_archived: false
                })
                .then(()=> {
                    tasksStore.getTasks()
                    appStore.showTaskDialog = false;
                    appStore.snackbarText = 'Task is successfully created'
                    appStore.showSnackbar = true
                    this.task = {}
                })
                .catch((errors) => {
                    console.log(errors);
                    if (errors) {}
                });

            } else {

                this.updateTask()

            }
        },
        updateTask() {
            const tasksStore = useTasksStore();
            const taskStore = useTaskStore();
            const appStore = useAppStore();

            window.axios.put('/api/task/'+this.task.id, this.task)
            .then(()=> {
                tasksStore.getTasks()
                appStore.showTaskDialog = false;
                appStore.snackbarText = 'Task is successfully updated'
                appStore.showSnackbar = true
                this.task = {}
            })
            .catch((errors) => {
                console.log(errors);
                if (errors) {}
            });
        }

    }
})