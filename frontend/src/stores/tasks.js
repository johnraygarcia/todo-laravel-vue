import { defineStore } from "pinia";
import { useTaskStore } from "./task";
import { useTaskFiltersStore } from "./taskFilters";

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        isLoading: false,
        currentPage: null,
        lastPage: null,
    }),
    getters: {
        // computed methods
    },
    actions: {
        reset () {
            this.tasks = {}
        },
        async getTasks(){
            this.isLoading = true;
            const taskStore = useTaskStore();
            const taskFiltersStore = useTaskFiltersStore()
            const {data, response} = await window.axios.get('/api/task', { params : {page : this.currentPage, searchKey: taskFiltersStore.filter.name}})

            this.tasks = await Promise.all(data.data.map(async(task) => {

                // asign the tags
                const taskTags = await taskStore.getTaskTags(task.id)

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

                return { ...task, priorityLevel: pl, tags: taskTags}
            }));
            this.currentPage = data.data.current_page;
            this.lastPage = data.data.last_page;
            this.isLoading = false;
        },
        async delete(id) {
            await window.axios.delete('/api/task/' + id).then(result => {
                this.getTasks()
            })
        }
    }
})