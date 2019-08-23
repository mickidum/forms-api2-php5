import Vue from "vue";
import Vuex from "vuex";
import axios from "axios";
import { config } from "./config";
import router from "./router";

Vue.use(Vuex);

const apiUrl =
  process.env.NODE_ENV === "production" ? config.prodUrl : config.devUrl;

export default new Vuex.Store({
  state: {
    status: "",
    token: localStorage.getItem("token") || "",
    allForms: [],
    currentForm: null,
    loading: false
  },
  mutations: {
    auth_request(state) {
      state.status = "loading";
    },
    loading(state) {
      state.loading = !state.loading;
    },
    auth_success(state, token) {
      state.status = "success";
      state.token = token;
    },
    auth_error(state) {
      state.status = "error";
    },
    logout(state) {
      state.status = "";
      state.token = "";
    },
    fillForms(state, allForms) {
      state.allForms = allForms;
    },
    currentForm(state, form) {
      state.currentForm = form;
    },
    filteredCurrentForm(state, items) {
      state.currentForm.items = items;
    },
    clearCurrentForm(state) {
      state.currentForm = null;
    },
    clearAllForms(state) {
      state.allForms = [];
    },
    deleteForm(state, form_id) {
      state.allForms = state.allForms.filter(form => {
        return form.form_id !== form_id;
      });
    }
  },
  actions: {
    login({ commit }, user) {
      commit("auth_request");
      axios({ url: `${apiUrl}/token`, data: user, method: "POST" })
        .then(resp => {
          const token = resp.data.token;
          // const user = resp.data.user;
          localStorage.setItem("token", token);
          axios.defaults.headers.common["Authorization"] = "Bearer " + token;
          commit("auth_success", token);
          router.push("/");
        })
        .catch(err => {
          commit("auth_error");
          localStorage.removeItem("token");
        });
    },
    logout({ commit }) {
      commit("logout");
      delete axios.defaults.headers.common["Authorization"];
      localStorage.removeItem("token");
      router.push("/login");
    },
    fillForms({ commit, dispatch }) {
      axios
        .get(`${apiUrl}/getlist`)
        .then(resp => {
          const allForms = resp.data ? resp.data : [];
          commit("fillForms", allForms);
        })
        .catch(err => {
          // console.log(err.response)
          if (err.response.status === 401) {
            dispatch("logout").then(() => {
              router.push("/login");
            });
          }
        });
    },
    fillCurrentForm({ commit, dispatch }, form_id) {
      commit("clearCurrentForm");
      axios
        .get(`${apiUrl}/getform/${form_id}`)
        .then(resp => {
          const form = resp.data ? resp.data : {};
          commit("currentForm", form);
        })
        .catch(err => {
          // console.log(err.response)
          if (err.response.status === 401) {
            dispatch("logout").then(() => {
              router.push("/login");
            });
          }
        });
    },
    removeCheckedItems({ commit, getters }, items) {
      commit("filteredCurrentForm", items);
    },
    updateCurrentForm({ commit, dispatch }, form) {
      axios
        .put(`${apiUrl}/updateform/${form.form_id}`, form)
        .then(resp => {
          const newForm = resp.data ? resp.data : {};
          commit("currentForm", newForm);
        })
        .catch(err => {
          if (err.response.status === 401) {
            dispatch("logout").then(() => {
              router.push("/login");
            });
          }
        });
    },
    deleteForm({ commit, dispatch }, form) {
      commit("deleteForm", form.form_id);
      axios
        .put(`${apiUrl}/deleteform/${form.form_id}`, this.state.allForms)
        .then(resp => {
          commit("deleteForm", form);
        })
        .catch(err => {
          if (err.response.status === 401) {
            dispatch("logout").then(() => {
              router.push("/login");
            });
          }
        });
    }
  },
  getters: {
    isLoggedIn: state => !!state.token,
    authStatus: state => state.status,
    getAllForms: state => state.allForms,
    getCurrentForm: state => state.currentForm
  }
});
