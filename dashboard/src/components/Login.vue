<template>
  <div class="edit-modal">
    <div class="inner inner-login">
      <div>
        <form class="login pure-form pure-form-stacked" @submit.prevent="login">
          <h1>DASHBOARD</h1>
          <label>
            Login
            <input
              class="input-item1"
              required
              v-model="username"
              type="text"
              placeholder="Username"
            />
          </label>
          <label>
            Password
            <input
              class="input-item1"
              required
              v-model="password"
              type="password"
              placeholder="Password"
            />
          </label>
          <p>
            <button type="submit" class="pure-button pure-button-primary">Login</button>
          </p>
        </form>
        <p :class="[status === 'error' ? 'error' : '']">{{status}}</p>
      </div>
      <p>
        For reset login and password delete file '.env'
        and
        <a
          style="color:#0078e7;"
          :href="userUrl"
        >follow link</a>
      </p>
    </div>
  </div>
</template>

<script>
import { config } from "../config";
const newUserUrl =
  process.env.NODE_ENV === "production" ? config.userUrl : config.userDevUrl;

export default {
  name: "Login",
  data() {
    return {
      username: "",
      password: "",
      userUrl: newUserUrl
    };
  },
  computed: {
    status() {
      return this.$store.getters.authStatus;
    }
  },
  methods: {
    login: function() {
      let username = this.username;
      let password = this.password;
      this.$store.dispatch("login", { username, password });
    }
  }
};
</script>