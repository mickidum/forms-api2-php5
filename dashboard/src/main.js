import Vue from "vue";
import App from "./App.vue";
import router from "./router";

import Axios from "axios";
import store from "./store";

import "./assets/css/pure-min.css";
import "./assets/css/grids-responsive-min.css";
import "./assets/css/main.scss";

Vue.config.productionTip = false;

Vue.prototype.$http = Axios;

const token = localStorage.getItem("token");
if (token) {
  Vue.prototype.$http.defaults.headers.common["Authorization"] =
    "Bearer " + token;
}

Vue.prototype.$http.interceptors.response.use(
  response => {
    return response;
  },
  error => {
    store.dispatch("logout");
    return Promise.reject(error);
  }
);

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount("#app");
