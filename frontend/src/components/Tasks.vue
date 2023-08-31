<script setup>
import { onMounted, ref } from 'vue'
import { useTasksStore } from '../stores/tasks'

const drawer = ref(null)
const taskStore = useTasksStore();

onMounted(() => {
  taskStore.getTasks();
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
          :onUpdate:search="searchTasks"
          :onClick:clear="clearSearch"
          :items="tasks"
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
        v-for="task in taskStore.tasks"
        :key="task.id"
        cols="12"
        md="4"
        lg="3"
      >
        <v-card :key="task.id" :color="!(task.isCompleted || task.isArchived) ? 'primary' : ''">
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
                v-if="task.isCompleted"
                color="success"
                prepend-icon="mdi-check"
                size="x-small"
                text="DONE"
                class="mr-2"
              ></v-chip>
              <v-chip
                v-if="task.isArchived"
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
            <span class="mr-1" v-for="tag in task.tags" :key="tag.key">
              <v-chip size="x-small" label :text="truncateTag(tag.title)"></v-chip>
            </span>
          </div>

          <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn
              size="small"
              variant="text"
              :icon="task.isCompleted ? 'mdi-undo' : 'mdi-check'"
              :disabled="task.isArchived"
              aria-label="toggle isComplete"
              @click="toggleIsCompleted(task.id)"
            ></v-btn>

            <v-btn
              size="small"
              variant="text"
              :icon="task.isArchived ? 'mdi-undo' : 'mdi-archive'"
              :disabled="task.isCompleted"
              aria-label="toggle isArchive"
              @click="toggleIsArchived(task.id)"
            ></v-btn>

            <v-menu transition="scale-transition">
              <template v-slot:activator="{ props }">
                <v-btn
                  icon="mdi-dots-vertical"
                  v-bind="props"
                  :disabled="(task.isCompleted || task.isArchived)"
                ></v-btn>
              </template>

              <v-list>
                <v-list-item
                  prependIcon="mdi-pencil"
                  title="Edit"
                ></v-list-item>

                <v-list-item
                  :disabled="task.isCompleted || task.isArchived"
                  @click="onClickDelete(task)"
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

    <v-row justify="center">
      <v-dialog
        v-model="dialog"
        persistent
        width="1024"
      >
        <template v-slot:activator="{ props }">
          <v-layout-item
            model-value position="bottom"
            class="text-end"
            size="88"
          >
          <div class="ma-4">
            <v-btn
              v-bind="props"
              icon="mdi-plus"
              size="large"
              color="secondary"
              elevation="8"
            />
          </div>
        </v-layout-item>
        </template>

        <v-card class="pa-4">
          <v-card-text>
            <v-card-title>
              <span class="text-h5">Add Task</span>
            </v-card-title>
            <v-container>
              <v-row>
                <v-col
                  cols="12"
                  sm="6"
                  md="6"
                >
                  <v-text-field
                    label="Title*"
                    variant="outlined"
                    required
                  ></v-text-field>

                  <v-textarea
                    clearable
                    label="Description*"
                    variant="outlined"
                    required
                  ></v-textarea>
                </v-col>


                <v-col
                  cols="12"
                  sm="6"
                  md="6"
                >
                  <v-radio-group
                    inline
                    label="Priorty"
                  >
                    <v-radio
                        v-for="{value, label} in priorityLevelOptions"
                        :key="value"
                        :value="value"
                        :label="label"
                        @click="filterByPriority(value)"
                    />
                  </v-radio-group>

                  <VueDatePicker
                    class="mb-6"
                    clearable
                    placeholder="Select Due Date"
                    ignore-time-validation
                    teleport-center
                    hide-input-icon
                    :hide-navigation="['time', 'minute', 'hours', 'seconds']"
                    :enable-time-picker="false"
                    format="yyyy-MM-dd"
                    :teleport="true"
                  />

                  <v-select
                    clearable
                    chips
                    label="Tags"
                    :items="['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']"
                    multiple
                    variant="outlined"
                  ></v-select>
                </v-col>
              </v-row>
            </v-container>
            <small class="text-red">*indicates required field</small>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              variant="text"
              @click="dialog = false"
            >
              Close
            </v-btn>
            <v-btn
              color="primary"
              variant="flat"
              @click="dialog = false"
            >
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>
  </v-container>

  <v-dialog v-model="showDeleteDialog" max-width="500px">
    <v-card class="pa-4">
      <v-card-title>Delete Task</v-card-title>
      <v-card-text class="px-4">Are you sure you want to delete <b>{{taskToDelete.title}}</b>? This action cannot be undone.</v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="showDeleteDialog = false">Close</v-btn>
        <v-btn color="red"  variant="flat" text @click="deleteTask(taskToDelete.id)">Delete</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-snackbar
    v-model="snackbar.showSnackbar"
    :timeout="snackbar.timeout"
  >
    {{ snackbar.text }}
    <template v-slot:actions>
      <v-btn
        color="blue"
        variant="text"
        @click="snackbar = false"
      >
        Close
      </v-btn>
    </template>
  </v-snackbar>
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

/*var dummyTasks = [
  {
    id: 1,
    title: "MERN Review",
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc aliquam justo eu scelerisque commodo. Sed ut leo vel justo maximus tempor et et arcu. Curabitur vitae tempor ligula. Praesent gravida, sapien ac aliquam viverra, arcu nisl condimentum arcu, quis luctus libero justo sit amet purus. Suspendisse volutpat, neque et viverra scelerisque, erat purus dapibus sapien, imperdiet sodales sapien leo eu turpis. Curabitur quam sapien, elementum quis erat et, vehicula pretium justo. Quisque aliquet in justo nec iaculis. Aliquam porta neque leo, eu suscipit turpis ornare vel. Proin venenatis, dolor non bibendum semper, tellus ex consectetur sem, ut molestie augue tellus quis purus. Morbi vel eleifend nisi. Integer tellus ipsum, fringilla vel neque id, sodales accumsan ipsum.",
    dueDate: new Date("2023-01-25"),
    createdAt: new Date("2023-01-1"),
    completedAt: null,
    archivedAt: null,
    priorityLevel: {
      key: "urgent",
      value: 1,
    },
    isCompleted: false,
    isArchived: false,
    tags: [
      {title: "MERN", key: "mern" },
      {title: "Programming", key: "programming"},
      {title: "Review", key: "review"},
      {title: "Job Hunt", key: "jobHunt"}
    ]
  },
  {
    id: 2,
    title: "TypeScript Review",
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc aliquam justo eu scelerisque commodo. Sed ut leo vel justo maximus tempor et et arcu. Curabitur vitae tempor ligula. Praesent gravida, sapien ac aliquam viverra, arcu nisl condimentum arcu, quis luctus libero justo sit amet purus. Suspendisse volutpat, neque et viverra scelerisque, erat purus dapibus sapien, imperdiet sodales sapien leo eu turpis. Curabitur quam sapien, elementum quis erat et, vehicula pretium justo. Quisque aliquet in justo nec iaculis. Aliquam porta neque leo, eu suscipit turpis ornare vel. Proin venenatis, dolor non bibendum semper, tellus ex consectetur sem, ut molestie augue tellus quis purus. Morbi vel eleifend nisi. Integer tellus ipsum, fringilla vel neque id, sodales accumsan ipsum.",
    dueDate: new Date("2023-02-1"),
    createdAt: new Date("2023-01-2"),
    completedAt: null,
    archivedAt: null,
    priorityLevel: {
      key: "high",
      value: 2,
    },
    isCompleted: false,
    isArchived: false,
    tags: [
      {title: "TypeScript", key: "typescript" },
      {title: "Programming", key: "programming"},
      {title: "Review", key: "review"}
    ]
  },
  {
    id: 3,
    title: "Enroll Emma",
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc aliquam justo eu scelerisque commodo. Sed ut leo vel justo maximus tempor et et arcu. Curabitur vitae tempor ligula. Praesent gravida, sapien ac aliquam viverra, arcu nisl condimentum arcu, quis luctus libero justo sit amet purus. Suspendisse volutpat, neque et viverra scelerisque, erat purus dapibus sapien, imperdiet sodales sapien leo eu turpis. Curabitur quam sapien, elementum quis erat et, vehicula pretium justo. Quisque aliquet in justo nec iaculis. Aliquam porta neque leo, eu suscipit turpis ornare vel. Proin venenatis, dolor non bibendum semper, tellus ex consectetur sem, ut molestie augue tellus quis purus. Morbi vel eleifend nisi. Integer tellus ipsum, fringilla vel neque id, sodales accumsan ipsum.",
    dueDate: new Date("2023-01-10"),
    createdAt: new Date("2023-01-5"),
    completedAt: null,
    archivedAt: new Date("2023-01-25"),
    priorityLevel: {
      key: "normal",
      value: 3,
    },
    isCompleted: false,
    isArchived: true,
    tags: [
      {title: "Emma", key: "emma" },
      {title: "Homeschooling", key: "homeschooling"},
    ]
  },
  {
    id: 4,
    title: "Set up Study Area",
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc aliquam justo eu scelerisque commodo. Sed ut leo vel justo maximus tempor et et arcu. Curabitur vitae tempor ligula. Praesent gravida, sapien ac aliquam viverra, arcu nisl condimentum arcu, quis luctus libero justo sit amet purus. Suspendisse volutpat, neque et viverra scelerisque, erat purus dapibus sapien, imperdiet sodales sapien leo eu turpis. Curabitur quam sapien, elementum quis erat et, vehicula pretium justo. Quisque aliquet in justo nec iaculis. Aliquam porta neque leo, eu suscipit turpis ornare vel. Proin venenatis, dolor non bibendum semper, tellus ex consectetur sem, ut molestie augue tellus quis purus. Morbi vel eleifend nisi. Integer tellus ipsum, fringilla vel neque id, sodales accumsan ipsum.",
    dueDate: new Date("2023-04-20"),
    createdAt: new Date("2023-04-01"),
    completedAt: new Date("2023-05-01"),
    archivedAt: null,
    priorityLevel: {
      key: "low",
      value: 4,
    },
    isCompleted: true,
    isArchived: false,
    tags: [
      {title: "Adam", key: "adam" },
      {title: "Homeschooling", key: "homeschooling"},
    ]
  },
  {
    id: 5,
    title: "Practice Coding Challege",
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc aliquam justo eu scelerisque commodo. Sed ut leo vel justo maximus tempor et et arcu. Curabitur vitae tempor ligula. Praesent gravida, sapien ac aliquam viverra, arcu nisl condimentum arcu, quis luctus libero justo sit amet purus. Suspendisse volutpat, neque et viverra scelerisque, erat purus dapibus sapien, imperdiet sodales sapien leo eu turpis. Curabitur quam sapien, elementum quis erat et, vehicula pretium justo. Quisque aliquet in justo nec iaculis. Aliquam porta neque leo, eu suscipit turpis ornare vel. Proin venenatis, dolor non bibendum semper, tellus ex consectetur sem, ut molestie augue tellus quis purus. Morbi vel eleifend nisi. Integer tellus ipsum, fringilla vel neque id, sodales accumsan ipsum.",
    dueDate: new Date("2023-03-05"),
    createdAt: new Date("2023-03-01"),
    completedAt: null,
    archivedAt: new Date("2023-06-01"),
    priorityLevel: {
      key: "normal",
      value: 3,
    },
    isCompleted: false,
    isArchived: true,
    tags: [
      {title: "Programming", key: "programming"},
      {title: "Review Review Review Review", key: "review"}
    ]
  },
  {
    id: 6,
    title: "Submit applications",
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc aliquam justo eu scelerisque commodo. Sed ut leo vel justo maximus tempor et et arcu. Curabitur vitae tempor ligula. Praesent gravida, sapien ac aliquam viverra, arcu nisl condimentum arcu, quis luctus libero justo sit amet purus. Suspendisse volutpat, neque et viverra scelerisque, erat purus dapibus sapien, imperdiet sodales sapien leo eu turpis. Curabitur quam sapien, elementum quis erat et, vehicula pretium justo. Quisque aliquet in justo nec iaculis. Aliquam porta neque leo, eu suscipit turpis ornare vel. Proin venenatis, dolor non bibendum semper, tellus ex consectetur sem, ut molestie augue tellus quis purus. Morbi vel eleifend nisi. Integer tellus ipsum, fringilla vel neque id, sodales accumsan ipsum.",
    dueDate: new Date("2023-03-01"),
    createdAt: new Date("2023-01-10"),
    completedAt: new Date("2023-05-10"),
    archivedAt: null,
    priorityLevel: {
      key: "urgent",
      value: 1,
    },
    isCompleted: true,
    isArchived: false,
    tags: []
  },
]*/

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
    search: null,
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
    dialog: false
  }),
  methods: {
    toggleIsCompleted(id) {
      let task = this.tasks.filter(task => task.id === id)[0]
      task.isCompleted = !task.isCompleted
      this.snackbar = {
        ...this.snackbar,
        showSnackbar: true,
        text: task.isCompleted ? "Succesfully marked item as COMPLETED" : "Successfully restored item to TO DO"
      }
    },
    toggleIsArchived(id) {
      let task = this.tasks.filter(task => task.id === id)[0]
      task.isArchived = !task.isArchived
      this.snackbar = {
        ...this.snackbar,
        showSnackbar: true,
        text: task.isArchived ? "Succesfully archived item" : "Successfully unarchived item"
      }
    },
    onClickDelete(task) {
      this.taskToDelete = task
      this.showDeleteDialog = !this.showDeleteDialog
    },
    deleteTask(id) {
      this.tasks = this.tasks.filter(task => task.id !== id)
      this.showDeleteDialog = false
      this.taskToDelete = {}
      this.snackbar = {
        ...this.snackbar,
        showSnackbar: true,
        text: "Succesfully deleted item"
      }
    },
    searchTasks(searchKey) {
      var regex = new RegExp(searchKey, 'i');

      this.tasks = this.tasks.filter(task => regex.test(task.title))

    },
    clearSearch() {
      // this.tasks = dummyTasks
    },
    getFormattedDate(date) {
      if(!date) return ""

      var year = date.getFullYear().toString();
      var month = (date.getMonth() + 101).toString().substring(1);
      var day = (date.getDate() + 100).toString().substring(1);
      return year + "-" + month + "-" + day;
    },
    getSubtext(task) {
      if(task.isCompleted) return this.getFormattedDate(task.completedAt)
      else if (task.isArchived) return this.getFormattedDate(task.archivedAt)
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
    }
  },
  computed: {},

}
</script>