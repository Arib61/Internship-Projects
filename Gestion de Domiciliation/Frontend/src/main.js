// main.js
import { createApp } from "vue";
import App from "@/App.vue";
import axios from "axios";

// --- CSS imports ---
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap";
import "bootstrap-vue/dist/bootstrap-vue.css";
import "ant-design-vue/dist/reset.css";
import "sweetalert2/dist/sweetalert2.min.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "boxicons/css/boxicons.min.css";
import "pe7-icon/dist/dist/pe-icon-7-stroke.css";
import "typicons.font/src/font/typicons.css";
import "weathericons/css/weather-icons.css";
import "ionicons-npm/css/ionicons.css";
import "@/assets/css/feather.css";
import "@/assets/css/style.css";
import { router } from "@/router";
import Swal from "sweetalert2";


// --- Axios config ---
axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.withCredentials = false; // Set true if using cookies like Laravel Sanctum
// Setup interceptor
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("access_token");
    const publicEndpoints = ["/login", "/register"];
    const isPublic = publicEndpoints.some((endpoint) =>
      config.url.includes(endpoint)
    );

    if (!isPublic && token) {
      config.headers["Authorization"] = `Bearer ${token}`;
    } else {
      delete config.headers["Authorization"];
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Intercepteur de réponses
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 403) {
      const message = error.response.data?.message;

      if (message?.toLowerCase().includes("abonnement expiré")) {
        localStorage.removeItem("access_token");

        Swal.fire({
          icon: "error",
          title: "Abonnement expiré",
          text: "Votre session a expiré. Merci de contacter le support.",
        });

        router.push({ name: "login" });
      }
    }

    return Promise.reject(error);
  }
);

// --- Plugins ---
import { BootstrapVue3, BToastPlugin } from "bootstrap-vue-3";
import VueApexCharts from "vue3-apexcharts";
import VueFeather from "vue-feather";
import VueSweetalert2 from "vue-sweetalert2";
import Vue3Autocounter from "vue3-autocounter";
import VueSelect from "vue3-select2-component";
import DatePicker from "vue3-datepicker";
import StarRating from "vue-star-rating";
import FlagIcon from "vue-flag-icon";
import VueFormWizard from "vue3-form-wizard";
import VueEasyLightbox from "vue-easy-lightbox";
import Antd from "ant-design-vue";
import ThemifyIcon from "vue-themify-icons";
import SimpleLineIcons from "vue-simple-line";

// --- Components: Layout ---
import Header from "@/views/layouts/pos-header.vue";
import Sidebar from "@/views/layouts/pos-sidebar.vue";
import UserMenu from "@/views/layouts/user-menu.vue";
import FilesSidebar from "@/views/layouts/files-sidebar.vue";
import SettingsSidebar from "@/views/layouts/settings-sidebar.vue";
import CollapsedSidebar from "@/views/layouts/collapsed-sidebar.vue";
import HorizontalSidebar from "@/views/layouts/horizontal-sidebar.vue";
import SideSettings from "@/views/layouts/side-settings.vue";

// --- Modals ---
import DomicliationModal from "@/components/modal/domiciliation-modal.vue";
import UserModal from "@/components/modal/users-modal.vue";
import GestionnaireModal from "@/components/modal/gestionnaire-modal.vue";
import TemplateModal from "@/components/modal/template-modal.vue";

// --- Pages ---
import Login from "@/views/pages/authentication/Login.vue";
import Register from "@/views/pages/authentication/Register.vue";

// --- SweetAlert Global Instance ---
import swal from "sweetalert2";
window.Swal = swal;

// --- Create Vue App ---
const app = createApp(App);

// --- Global Axios ---
app.config.globalProperties.$axios = axios;

// --- Register Global Components ---
app.component("layout-header", Header);
app.component("layout-sidebar", Sidebar);
app.component("user-menu", UserMenu);
app.component("files-sidebar", FilesSidebar);
app.component("settings-sidebar", SettingsSidebar);
app.component("collapsed-sidebar", CollapsedSidebar);
app.component("horizontal-sidebar", HorizontalSidebar);
// app.component("side-settings", SideSettings);
app.component("domiciliation-modal", DomicliationModal);
app.component("users-modal", UserModal);
app.component("gestionnaire-modal", GestionnaireModal);
app.component("template-modal", TemplateModal);

app.component("login", Login);
app.component("register", Register);

app.component(VueFeather.name, VueFeather);

// --- Use Plugins ---
app.use(router);
app.use(BootstrapVue3);
app.use(BToastPlugin);
app.use(VueSweetalert2);
app.use(VueApexCharts);
app.use(FlagIcon);
app.use(VueFormWizard);
app.use(VueEasyLightbox);
app.use(Antd);
app.use(ThemifyIcon);
app.use(SimpleLineIcons);

// --- Mount the app ---
app.mount("#app");
