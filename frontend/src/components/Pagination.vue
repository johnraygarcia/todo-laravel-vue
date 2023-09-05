<template>
    <v-pagination
        v-model="tasksStore.currentPage"
        :total-visible="10"
        :length="tasksStore.lastPage">
    </v-pagination>
</template>
<script setup>

import { useTasksStore } from '@/stores/tasks';
import { storeToRefs } from 'pinia';
import { watch } from 'vue';
const tasksStore = useTasksStore();
const { currentPage } = storeToRefs(tasksStore);
watch(currentPage, () => {
    tasksStore.getTasks();
});
</script>