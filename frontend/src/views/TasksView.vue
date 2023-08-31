<template>
  <Tasks />
</template>

<script>
import { defineComponent } from 'vue';
import { useTasksStore } from '../stores/tasks'

// Components
import Tasks from '../components/Tasks.vue';

export default defineComponent({
  name: 'TasksView',

  components: {
    Tasks,
  },
});
</script>
