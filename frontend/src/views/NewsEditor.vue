<template>
  <div class="news-container">

    <h1>News erstellen</h1>

    <form @submit.prevent="saveNews">

      <div class="form-group">
        <label>Titel</label>
        <input
          v-model="title"
          type="text"
          placeholder="Titel eingeben"
        />
      </div>

      <div class="form-group">
        <label>Inhalt</label>
        <textarea
          v-model="content"
          rows="10"
          placeholder="News schreiben..."
        ></textarea>
      </div>

      <button
        type="submit"
        :disabled="loading"
      >
        {{ loading ? 'Speichern...' : 'News speichern' }}
      </button>

    </form>

    <div
      v-if="message"
      class="message"
    >
      {{ message }}
    </div>

  </div>
</template>

<script>
export default {
  name: "NewsEditor",

  data() {
    return {
      title: "",
      content: "",
      loading: false,
      message: ""
    }
  },

  methods: {
    async saveNews() {

      if (!this.title || !this.content) {
        this.message = "Bitte alle Felder ausfüllen";
        return;
      }

      this.loading = true;
      this.message = "";

      try {
        const response = await fetch(
          "/api/save_news.php",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              title: this.title,
              content: this.content
            })
          }
        );

        let result;

        try {
          result = await response.json();
        } catch (e) {
          result = {message: "Ungültige Serverantwort"};
        }

        // ✅ Erfolg (z. B. 201 Created)
        if (response.ok) {
          this.message = result.message || "News erfolgreich gespeichert";

          if (result.news_id) {
            console.log("Neue News ID:", result.news_id);
          }

          this.title = "";
          this.content = "";
          return;
        }

        // ❗ Validierungsfehler
        if (response.status === 422) {
          this.message = result.message || "Validierungsfehler";
          return;
        }

        // ❗ Bad Request
        if (response.status === 400) {
          this.message = result.message || "Ungültiges JSON";
          return;
        }

        // ❗ Methode nicht erlaubt
        if (response.status === 405) {
          this.message = "Methode nicht erlaubt";
          return;
        }

        // ❗ Serverfehler / Fallback
        this.message = result.message || "Interner Fehler";

      } catch (error) {
        console.error(error);
        this.message = "Netzwerkfehler beim Speichern";
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>

.news-container {
  max-width: 900px;
  margin: 40px auto;
  padding: 20px;
}

h1 {
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: bold;
}

input,
textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
}

button {
  padding: 12px 24px;
  border: none;
  cursor: pointer;
  border-radius: 6px;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  margin-top: 20px;
  padding: 12px;
  border-radius: 6px;
  background: #f3f3f3;
}

</style>
