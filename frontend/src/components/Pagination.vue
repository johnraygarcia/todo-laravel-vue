<template>
    <v-pagination
        v-model="tasksStore.currentPage"
        :total-visible="10"
        :length="tasksStore.lastPage">
    </v-pagination>
</template>
<script setup>

import { useTasksStore } from '@/stores/tasks';
const tasksStore = useTasksStore();

// export default {

    // computed: {
    //     currentPage: {
    //         get() {

    //             return tasksStore.tasks.current_page
    //         },
    //         set() {

    //         }
    //     },
    //     lastPage: {
    //         get() {
    //             return this.tasksStore.tasksStore.tasks.last_page
    //         }
    //     }
    // }
// }
</script>