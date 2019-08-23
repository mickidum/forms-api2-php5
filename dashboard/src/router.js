import Vue from "vue";
import Router from "vue-router";
import store from "./store";
import FormList from "./views/FormList";
import Form from "./views/Form";
import Login from "./components/Login";

Vue.use(Router);

let router = new Router({
  mode: "history",
  base: process.env.BASE_URL,
  routes: [
    {
      path: "/",
      name: "formList",
      component: FormList,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: "/login",
      name: "login",
      component: Login
    },
    {
      path: "/:form_id",
      name: "form",
      component: Form,
      meta: {
        requiresAuth: true
      }
    }
  ]
});

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!store.getters.isLoggedIn) {
      next("/login");
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
