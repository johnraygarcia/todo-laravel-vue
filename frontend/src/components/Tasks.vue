<script setup>
import { onBeforeMount, ref } from 'vue'
import { useTasksStore } from '../stores/tasks'
import TaskForm from '../components/TaskForm.vue'
import Pagination from '../components/Pagination.vue'
import Snackbar from '../components/Snackbar.vue'

const drawer = ref(null)
const tasksStore = useTasksStore();
const appStore = useAppStore();

onBeforeMount(() => {
  appStore.snackbarText = 'Loading...'
  appStore.snackbarColor = 'info'
  appStore.toggleSnackbar()
  tasksStore.getTasks();
})

</script>
<template v-slot:prepend>
  <v-container class="tasks pa-6">
    <v-row align="start" justify="center">
      <v-col>
        <v-autocomplete
          auto-select-first
          clearable
          :loading="taskStore.isLoading"
          v-model="searchKey"
          @update:search="searchTasks"
          :items="tasksStore.tasks"
          item-title="title"
          item-value="title"
          label="Search tasks"
          append-inner-icon="mdi-magnify"
          density="comfortable"
          variant="solo"
        ></v-autocomplete>
      </v-col>

      <v-col cols="auto">
        <v-btn :icon="drawer ? 'mdi-chevron-up' : 'mdi-chevron-down'" @click.stop="drawer = !drawer"></v-btn>
      </v-col>
    </v-row>

    <v-card density="compact" :class="drawer ? 'mb-6' : ''" flat>
      <v-expand-transition>
        <div v-show="drawer">
          <v-row class="pa-4">
            <v-col>
              <div class="text-overline">
                <v-icon>mdi-filter</v-icon>
                FILTERS
              </div>

              <v-radio-group
                inline
                label="Completion Status"
                v-model="selectedCompletionStatus"
              >
                <v-radio
                    v-for="{value, label} in completionStatusOptions"
                    :key="value"
                    :value="value"
                    :label="label"
                    @click="filterByCompletionStatus(value)"
                />
              </v-radio-group>

              <v-radio-group
                inline
                label="Archive Status"
                v-model="selectedArchiveStatus"
              >
                <v-radio
                    v-for="{value, label} in archiveStatusOptions"
                    :key="value"
                    :value="value"
                    :label="label"
                    @click="filterByArchiveStatus(value)"
                />
              </v-radio-group>

              <v-radio-group
                inline
                label="Priorty"
                v-model="selectedPriorityLevel"
              >
                <v-radio
                    v-for="{value, label} in priorityLevelOptions"
                    :key="value"
                    :value="value"
                    :label="label"
                    @click="filterByPriority(value)"
                />
              </v-radio-group>

              <v-label class="mb-4 ml-4">Due Date (Date Range)</v-label>
              <VueDatePicker
                class="px-4"
                :model-value="dates" @update:model-value="onChangeDateRange"
                range
                clearable
                placeholder="Select Due Dates"
                ignore-time-validation
                teleport-center
                hide-input-icon
                :hide-navigation="['time', 'minute', 'hours', 'seconds']"
                :enable-time-picker="false"
                format="yyyy-MM-dd"
                :teleport="true"
                :partial-range="false"
                :multi-calendars="{ solo: true, density: 'comfortable' }"
              />
            </v-col>

            <v-col>
              <div class="text-overline">
                <v-icon>mdi-sort</v-icon>
                SORT
              </div>

              <v-radio-group
                inline
                label="Sort order"
                v-model="selectedSortOrder"
              >
                <v-radio
                    v-for="{value, label} in sortOrderOptions"
                    :key="value"
                    :value="value"
                    :label="label"
                    @click="sortTasks(value)"
                />
              </v-radio-group>

              <v-radio-group
                inline
                label="Sort by"
                v-model="selectedSortBy"
              >
                <v-radio
                    v-for="{value, label} in sortByOptions"
                    :key="value"
                    :value="value"
                    :label="label"
                    @click="sortTasks(value)"
                />
              </v-radio-group>

              <v-btn variant="tonal" @click="onReset">
                Reset
              </v-btn>
            </v-col>
          </v-row>
        </div>
      </v-expand-transition>
    </v-card>

    <v-row>
      <v-col
        v-if="tasksStore.tasks"
        v-for="task in tasksStore.tasks"
        :key="task.id"
        cols="12"
        md="4"
        lg="3"
      >
        <v-card :key="task.id" :color="!(task.status || task.is_archived) ? 'primary' : ''">
          <v-card-item>
            <v-card-title>{{ task.title }}</v-card-title>
            <v-card-subtitle >
              <v-chip
                :color="getPriorityDetails(task.priorityLevel.key).color"
                :prepend-icon="getPriorityDetails(task.priorityLevel.key).icon"
                size="x-small"
                :text="getPriorityDetails(task.priorityLevel.key).text"
                class="mr-2"
              ></v-chip>
              <v-chip
                v-if="task.status"
                color="success"
                prepend-icon="mdi-check"
                size="x-small"
                text="DONE"
                class="mr-2"
              ></v-chip>
              <v-chip
                v-if="task.is_archived"
                color="gray"
                prepend-icon="mdi-archive"
                size="x-small"
                text="ARCHIVED"
                class="mr-2"
              ></v-chip>
              <span>{{ getSubtext(task) }}</span>
            </v-card-subtitle>
          </v-card-item>

          <v-card-text>
            <p>{{ truncateDesc(task.description) }}</p>
          </v-card-text>

          <v-divider class="mx-4 mb-1"></v-divider>

          <div class="px-4">
            <span class="mr-1" v-for="tag in task.tags" :key="tag.id">
              <v-chip size="x-small" label :text="truncateTag(tag.name)"></v-chip>
            </span>
          </div>

          <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn
              size="small"
              variant="text"
              :icon="task.status ? 'mdi-undo' : 'mdi-check'"
              :disabled="task.is_archived"
              aria-label="toggle isComplete"
              @click="toggleIsCompleted(task)"
            ></v-btn>

            <v-btn
              size="small"
              variant="text"
              :icon="task.is_archived ? 'mdi-undo' : 'mdi-archive'"
              aria-label="toggle isArchive"
              @click="toggleIsArchived(task)"
            ></v-btn>

            <v-menu transition="scale-transition">
              <template v-slot:activator="{ props }">
                <v-btn
                  icon="mdi-dots-vertical"
                  v-bind="props"
                  :disabled="(task.status || task.is_archived)"
                ></v-btn>
              </template>

              <v-list>
                <v-list-item
                  prependIcon="mdi-pencil"
                  title="Edit"
                  @click="editTask(task)"
                ></v-list-item>

                <v-list-item
                  :disabled="task.status || task.is_archived"
                  @click="deleteTask(task, true)"
                  prependIcon="mdi-delete"
                  base-color="red"
                  title="Delete"
                ></v-list-item>
              </v-list>
            </v-menu>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <Pagination />
    <TaskForm />

  </v-container>

  <v-dialog v-model="showDeleteDialog" max-width="500px">
    <v-card class="pa-4">
      <v-card-title>Delete Task</v-card-title>
      <v-card-text class="px-4">Are you sure you want to delete <b>{{taskToDelete.title}}</b>? This action cannot be undone.</v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="showDeleteDialog = false">Close</v-btn>
        <v-btn color="red"  variant="flat" text @click="deleteTask(taskToDelete, false)">Delete</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <Snackbar />
</template>

<style lang="scss">
 .dp__input {
    min-height: 53px;
    border: 1px solid darkgray;
 }
</style>


<script>
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import { useTaskStore } from '@/stores/task';
import { useAppStore } from '@/stores/app';
import { useTaskFiltersStore } from '@/stores/taskFilters';
import { onBeforeMount } from 'vue'

var defaultFilters = {
  search: null,
  dates: null,
  completionStatus: "toDo",
  archiveStatus: "active",
  priorityLevel: null,
}

var defaultSort = {
  sortOrder: "asc",
  sortBy: "priorityLevel",
}

export default {
  name: "Tasks",
  data: () => ({
    showDeleteDialog: false,
    taskToDelete: {},
    snackbar: {
      showSnackbar: false,
      text: '',
      timeout: 2000,
    },
    selectedTask: null,
    searchKey: null,
    dates: null,
    completionStatusOptions: [
      { label: "To Do", value: "toDo"},
      { label: "Completed", value: "completed"},
    ],
    archiveStatusOptions: [
      { label: "Active", value: "active"},
      { label: "Archived", value: "archived"},
    ],
    priorityLevelOptions: [
      { label: "Urgent", value: "urgent"},
      { label: "High", value: "high"},
      { label: "Normal", value: "normal"},
      { label: "Low", value: "low"},
    ],
    sortOrderOptions: [
      { label: "Ascending", value: "asc"},
      { label: "Descending", value: "desc"},
    ],
    sortByOptions: [
      { label: "Created at", value: "createdAt"},
      { label: "Completed at", value: "completedAt"},
      { label: "Priority level", value: "priorityLevel"},
      { label: "Due date", value: "dueDate"},
    ],
    selectedCompletionStatus: null,
    selectedArchiveStatus: null,
    selectedPriorityLevel: null,
    selectedSortOrder: null,
    selectedSortBy: null,
    taskStore: useTaskStore(),
    tasksStore: useTasksStore(),
    appStore: useAppStore(),
    taskFilterStore: useTaskFiltersStore()
  }),
  methods: {
    toggleIsCompleted(task) {
      task.status = !task.status
      this.taskStore.task = task
      this.taskStore.updateTask()
    },
    toggleIsArchived(task) {
      task.is_archived = !task.is_archived
      this.taskStore.task = task
      this.taskStore.updateTask()
    },
    deleteTask(task, confirm) {
      this.taskToDelete = task
      if (confirm) {
        this.showDeleteDialog = !this.showDeleteDialog
      } else {
        this.tasksStore.delete(task.id)
        this.showDeleteDialog = false
        this.appStore.showSnackbar = true
        this.appStore.snackbarText = 'Successfully delete task'
      }
    },
    searchTasks() {

      this.taskFilterStore.filter.name = this.searchKey

      clearTimeout(this._debounceTimeout)

      this._debounceTimeout = setTimeout(() => {
        this.tasksStore.getTasks()
      }, 300)
    },
    clearSearch() {
      // this.tasks = dummyTasks
    },
    getFormattedDate(date) {
      if(!date) return ""
      var utcDate = new Date(date + ' UTC'); // laravel update_at date is saved in UTC
      var localDate = new Date(utcDate);
      return new Intl.DateTimeFormat('en-US').format(localDate) // converted to browser data/time
    },
    getSubtext(task) {
      if(task.isCompleted) return this.getFormattedDate(task.completedAt)
      else if (task.is_archived) return this.getFormattedDate(new Date(task.updated_at))
      return this.getFormattedDate(task.dueDate)
    },
    truncateDesc(desc) {
      return desc.length > 80 ? desc.substring(0, 80) + "..." : desc
    },
    truncateTag(tag) {
      return tag.length > 15 ? tag.substring(0, 15) + "..." : tag
    },
    getPriorityDetails(level) {
      switch(level) {
        case "urgent":
          return {
            color: "deep-orange",
            icon: "mdi-alarm-light",
            text: "URGENT"
          }
        case "high":
          return {
            color: "pink",
            icon: "mdi-alarm-light",
            text: "HIGH"
          }
        case "low":
          return {
            color: "blue-gray",
            icon: "mdi-alarm-light",
            text: "LOW"
          }
        default:
          return {
            color: "blue",
            icon: "mdi-alarm-light",
            text: "NORMAL"
          }
      }
    },
    filterByCompletionStatus(value) {
      if(value === "toDo") {
        this.tasks = dummyTasks
        this.tasks = this.tasks.filter(task => !task.isCompleted)
      } else {
        this.tasks = dummyTasks
        this.tasks = this.tasks.filter(task => task.isCompleted)
      }
    },
    filterByArchiveStatus(value) {
      if(value === "archived") {
        this.tasks = dummyTasks
        this.tasks = this.tasks.filter(task => task.isArchived)
      } else {
        this.tasks = dummyTasks
        this.tasks = this.tasks.filter(task => !task.isArchived)
      }
    },
    filterByPriority(value) {
      this.tasks = dummyTasks

      switch(value) {
        case "urgent":
          this.tasks = this.tasks.filter(task => task.priorityLevel.key === value)
          break;
        case "high":
          this.tasks = this.tasks.filter(task => task.priorityLevel.key === value)
          break;
        case "normal":
          this.tasks = this.tasks.filter(task => task.priorityLevel.key === value)
          break;
        case "low":
          this.tasks = this.tasks.filter(task => task.priorityLevel.key === value)
          break;
      }
    },
    sortTasks(order) {
      this.tasks = dummyTasks

      switch(this.selectedSortBy) {
        case "createdAt":
          if(order === "desc") {
            this.tasks = this.tasks.sort((a,b) => b.createdAt - a.createdAt)
            break;
          } else {
            this.tasks = this.tasks.sort((a,b) => a.createdAt - b.createdAt)
            break;
          }
        case "completedAt":
          if(order === "desc") {
            this.tasks = this.tasks.sort((a,b) => b.completedAt - a.completedAt)
            break;
          } else {
            this.tasks = this.tasks.sort((a,b) => a.completedAt - b.completedAt)
            break;
          }
        case "priorityLevel":
          if(order === "desc") {
            this.tasks = this.tasks.sort((a,b) => b.priorityLevel.value - a.priorityLevel.value)
            break;
          } else {
            this.tasks = this.tasks.sort((a,b) => a.priorityLevel.value - b.priorityLevel.value)
            break;
          }
        case "dueDate":
          if(order === "desc") {
            this.tasks = this.tasks.sort((a,b) => b.dueDate - a.dueDate)
            break;
          } else {
            this.tasks = this.tasks.sort((a,b) => a.dueDate - b.dueDate)
            break;
          }
      }
    },
    onChangeDateRange(dates) {
      this.dates = dates
      var [ start, end ] = dates;
      this.tasks = dummyTasks
      this.tasks = this.tasks.filter(task => task.dueDate >= start && task.dueDate <= end )
    },
    onReset() {
      var { search, dates, completionStatus, archiveStatus, priorityLevel } = defaultFilters
      var { sortOrder, sortBy } = defaultSort

      this.search = search
      this.dates = dates
      this.selectedCompletionStatus = completionStatus
      this.selectedArchiveStatus = archiveStatus
      this.selectedPriorityLevel = priorityLevel
      this.selectedSortOrder = sortOrder
      this.selectedSortBy = sortBy
      this.tasks = dummyTasks
    },
    async editTask(task) {

      // load the tags
      const tags = await this.taskStore.getTaskTags(task.id)
      task.tags = tags.map((tag)=> {
        return {...tag,id: tag.pivot.tag_id}
      })

      this.taskStore.task = task
      this.appStore.showTaskDialog = true;
    }
  }
}
</script>