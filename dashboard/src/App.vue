<template>
  <div id="app">
    <header class="main-header" id="nav">
      <router-link to="/">Home</router-link>
      <strong style="color: rgba(158, 158, 158, 0.18);">MICRO CRM</strong>
      <span class="logout">
        <span v-if="isLoggedIn" @click="logout">Logout</span>
        <router-link v-if="!isLoggedIn" to="/login">Login</router-link>
      </span>
    </header>

    <transition name="fade" mode="out-in">
      <router-view></router-view>
    </transition>
  </div>
</template>

<script>
export default {
  computed: {
    isLoggedIn() {
      return this.$store.getters.isLoggedIn;
    },
    getTime() {
      return Math.floor(Date.now() / 1000);
    }
  },
  methods: {
    logout() {
      this.$store.dispatch("logout");
    },
    refreshToken() {
      let now = +this.getTime;
      let expires = +localStorage.getItem("expires") || now;
      console.log(expires);
      console.log(now);
      let refresh = expires - now;
      console.log("Refresh :", refresh);
      if (refresh > 0) {
        let start = setInterval(() => {
          console.log("ping");
        }, refresh * 1000);
      }
    }
  }
};
</script>