import { createRouter, createWebHistory } from "vue-router";
import Login from "@/views/pages/authentication/Login.vue";
import Register from "@/views/pages/authentication/Register.vue";
import Dashboard from "@/views/pages/dashboard/Dashboard.vue";
import Settings from "@/views/pages/admin/Settings.vue";
import Domicliation from "@/views/pages/admin/Domicliation.vue";
import Users from "@/views/pages/admin/Users.vue";
import Clients from "@/views/pages/admin/Clients.vue";
import AddClient from "@/views/pages/admin/AddClient.vue";
import Template from "@/views/pages/admin/Template.vue";
import ShowClient from "@/views/pages/admin/ShowClient.vue";
import EditClient from "@/views/pages/admin/EditClient.vue";
import DomiciliationRenewHistory from "@/views/pages/admin/DomiciliationRenewHistory.vue";
import BurreauEquipeManager from "@/views/pages/admin/bureaux-equipe/BurreauEquipe.vue";
import BurreauEquipeRenewHistory from "@/views/pages/admin/bureaux-equipe/BurreauEquipeRenewHistory.vue";
import ResiliationsManagement from "@/views/pages/admin/bureaux-equipe/BurreauEquipeResiliations.vue";
import Profile from "@/views/pages/admin/Profile.vue";
import axios from 'axios';
import Swal from 'sweetalert2';
const routes = [
  {
    path: "/",
    redirect: "/login",
  },
  {
    path: "/login",
    name: "login",
    component: Login,
  },
  {
    path: "/register",
    name: "register",
    component: Register,
  },
  {
    path: "/logout",
    name: "logout",
    meta: { requiresAuth: true },
    beforeEnter: (to, from, next) => {
      localStorage.removeItem("access_token");
      localStorage.removeItem("user");
      localStorage.clear();
      next("/login");
    },
  },
  {
    path: "/dashboard",
    name: "dashboard",
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: "/settings",
    name: "settings",
    component: Settings,
    meta: { requiresAuth: true },
  },
  {
    path: "/domiciliations",
    name: "domiciliations",
    component: Domicliation,
    meta: { requiresAuth: true },
  },
  {
    path: "/resiliations",
    name: "ResiliationsList",
    component: () => import("@/views/pages/admin/Resiliations.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/admin/utilisateurs",
    name: "users",
    component: Users,
    meta: { requiresAuth: true },
  },
  {
    path: "/admin/clients",
    name: "clients",
    component: Clients,
    meta: { requiresAuth: true },
  },
  {
    path: "/admin/addclient",
    name: "addclient",
    component: AddClient,
    meta: { requiresAuth: true },
  },
  {
    path: "/admin/templates",
    name: "Template",
    component: Template,
    meta: { requiresAuth: true },
  },

  // AJOUTEZ CETTE ROUTE MANQUANTE
  {
    path: "/admin/clients/:id",
    name: "ShowClient",
    component: ShowClient,
    meta: { requiresAuth: true },
    props: true, // Ceci permet de passer l'ID comme prop au composant
  },
  {
    path: "/clients/:id/edit",
    name: "EditClient",
    component: EditClient,
    meta: { requiresAuth: true },
    props: true,
  },

  {
    path: "/admin/bureaux-equipe/BurreauEquipe",
    name: "BurreauEquipeManager",
    component: BurreauEquipeManager,
    meta: { requiresAuth: true },
  },

  {
    path: "/admin/bureaux-equipe/BurreauEquipeRenewHistory",
    name: "BurreauEquipeRenewHistory",
    component: BurreauEquipeRenewHistory,
    meta: { requiresAuth: true },
  },

  {
    path: "/admin/bureaux-equipe/BurreauEquipeResiliations",
    name: "ResiliationsManagement",
    component: ResiliationsManagement,
    meta: { requiresAuth: true },
  },

  {
    path: "/renouvellements",
    name: "DomiciliationRenewHistory",
    component: DomiciliationRenewHistory,
    meta: { requiresAuth: true },
  },
  {
    path: "/profile",
    name: "Profile",
    component: Profile,
    meta: { requiresAuth: true },
    props: true,
  },
];

export const router = createRouter({
  history: createWebHistory("/"),
  linkActiveClass: "active",
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Smooth scroll to top after route change
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve({ top: 0, behavior: "smooth" });
      }, 300); // Delay ensures page content is rendered
    });
  },
});

// Navigation guard for auth protection and redirection
const adminPages = ["/admin", "/settings", "/admin/users"]; // or use route names

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem("access_token");

  if (to.meta.requiresAuth && !token) {
    return next("/login");
  }

  if (to.meta.requiresAuth && token) {
    try {
      await axios.get("/abonnement/status", {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
    } catch (error) {
      if (error.response && error.response.status === 403) {
        localStorage.removeItem("access_token");

        Swal.fire({
          icon: "error",
          title: "Abonnement expiré",
          text: "Votre session a expiré. Merci de contacter le support.",
        });

        return next("/login");
      }
    }
  }

  if ((to.path === "/login" || to.path === "/register") && token) {
    return next("/dashboard");
  }

  return next();
});
