```vue
<template>
  <div class="todo-container">

    <div class="todo-card">

      <h1>TODO Liste</h1>

      <div class="add-form">
        <input
          v-model="newTodo"
          type="text"
          placeholder="Neue Aufgabe..."
          @keyup.enter="addTodo"
        >

        <button
          @click="addTodo"
          :disabled="loading"
        >
          Hinzufügen
        </button>
      </div>

      <div
        v-if="error"
        class="error-box"
      >
        {{ error }}
      </div>

      <div
        v-if="loading"
        class="loading"
      >
        Lade Daten...
      </div>

      <ul
        v-else
        class="todo-list"
      >
        <li
          v-for="todo in todos"
          :key="todo.id"
          :class="{ completed: Number(todo.completed) === 1 }"
        >
          <div class="todo-content">

            <input
              type="checkbox"
              :checked="Number(todo.completed) === 1"
              @change="toggleTodo(todo)"
            >

            <span>
                            {{ todo.title }}
                        </span>

          </div>

          <button
            class="delete-btn"
            @click="deleteTodo(todo.id)"
          >
            ✕
          </button>
        </li>

        <li
          v-if="todos.length === 0"
          class="empty-state"
        >
          Keine Todos vorhanden.
        </li>
      </ul>

    </div>

  </div>
</template>

<script>
export default {
  name: "TodoApp",

  data() {
    return {
      todos: [],
      newTodo: '',
      loading: false,
      error: null,

      endpoints: {
        list: '/api/getTodo.php',
        create: '/api/createTodo.php',
        update: '/api/updateTodo.php',
        delete: '/api/deleteTodo.php'
      }
    };
  },

  mounted() {
    this.loadTodos();
  },

  methods: {

    async loadTodos() {

      this.loading = true;
      this.error = null;

      try {

        const response = await fetch(
          this.endpoints.list
        );

        const result = await response.json();

        if (!result.success) {
          throw new Error(
            result.error || 'Fehler beim Laden'
          );
        }

        this.todos = result.data;

      } catch (err) {

        console.error(err);

        this.error = err.message;

      } finally {

        this.loading = false;
      }
    },

    async addTodo() {

      const title = this.newTodo.trim();

      if (!title) {
        return;
      }

      try {

        const response = await fetch(
          this.endpoints.create,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              title
            })
          }
        );

        const result = await response.json();

        if (!result.success) {
          throw new Error(
            result.error || 'Todo konnte nicht gespeichert werden'
          );
        }

        this.newTodo = '';

        await this.loadTodos();

      } catch (err) {

        console.error(err);

        this.error = err.message;
      }
    },

    async toggleTodo(todo) {

      try {

        const response = await fetch(
          this.endpoints.update,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id: todo.id,
              completed: Number(todo.completed) ? 0 : 1
            })
          }
        );

        const result = await response.json();

        if (!result.success) {
          throw new Error(
            result.error || 'Todo konnte nicht aktualisiert werden'
          );
        }

        await this.loadTodos();

      } catch (err) {

        console.error(err);

        this.error = err.message;
      }
    },

    async deleteTodo(id) {

      if (!confirm('Todo wirklich löschen?')) {
        return;
      }

      try {

        const response = await fetch(
          this.endpoints.delete,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id
            })
          }
        );

        const result = await response.json();

        if (!result.success) {
          throw new Error(
            result.error || 'Todo konnte nicht gelöscht werden'
          );
        }

        await this.loadTodos();

      } catch (err) {

        console.error(err);

        this.error = err.message;
      }
    }
  }
};
</script>

<style scoped>

.todo-container {
  display: flex;
  justify-content: center;
  padding: 40px 20px;
}

.todo-card {
  width: 100%;
  max-width: 800px;
  background: #ffffff;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

h1 {
  margin-bottom: 20px;
  font-size: 28px;
}

.add-form {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.add-form input {
  flex: 1;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.add-form button {
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  cursor: pointer;
}

.add-form button:disabled {
  opacity: .5;
}

.todo-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.todo-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;

  padding: 14px;
  border: 1px solid #e5e5e5;
  border-radius: 8px;

  margin-bottom: 10px;
}

.todo-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.completed span {
  text-decoration: line-through;
  opacity: .5;
}

.delete-btn {
  border: none;
  background: #dc3545;
  color: white;

  width: 36px;
  height: 36px;

  border-radius: 50%;
  cursor: pointer;
}

.error-box {
  background: #ffe5e5;
  color: #a40000;
  padding: 12px;
  margin-bottom: 15px;
  border-radius: 8px;
}

.loading {
  padding: 20px;
}

.empty-state {
  justify-content: center !important;
  color: #888;
}
</style>
```
