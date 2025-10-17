<template>
  <div class="account-page">
    <div class="account-content">
      <div class="login-wrapper bg-img d-flex">
        <!-- Form Section -->
        <div class="login-content d-flex justify-content- align-items-center flex-column position-relative">
          <Form @submit="onSubmit" :validation-schema="schema" style="mt-20" v-slot="{ errors }">
            <div class="d-flex justify-content-center mb-4">
              <img src="@/assets/img/logo.png" width="50%" alt="">
            </div>
            <div class="login-userset">
              <div class="login-userheading">
                <h3>Se connecter ou créer un compte</h3>
                <h4>
                  Connectez-vous à l'aide de votre compte pour accéder à nos services.
                </h4>
              </div>
              <div class="form-login">
                <label class="form-label">Adresse Email</label>
                <div class="form-addons">
                  <Field name="email" type="text" value="admin@example.com" class="form-control"
                    :class="{ 'is-invalid': errors.email }" />

                  <img v-if="!errors.email" src="@/assets/img/icons/mail.svg" alt="img" />

                  <!-- Error message -->
                  <div class="invalid-feedback">{{ errors.email }}</div>
                  <div class="emailshow text-danger" id="email"></div>
                </div>
              </div>
              <div class="form-login">
                <label>Mot de Passe</label>
                <div class="pass-group">
                  <Field name="password" :type="showPassword ? 'text' : 'password'" value="password123"
                    class="form-control pass-input mt-2" :class="{ 'is-invalid': errors.password }" />

                  <!-- Show eye/eye-slash icon only if no password error -->
                  <span v-if="!errors.password" @click="toggleShow" class="toggle-password">
                    <i :class="{
                      'fas fa-eye': showPassword,
                      'fas fa-eye-slash': !showPassword,
                    }"></i>
                  </span>

                  <!-- Error message -->
                  <div class="invalid-feedback">{{ errors.password }}</div>
                  <div class="emailshow text-danger" id="password"></div>
                </div>
              </div>
              <div class="form-login">
                <button class="btn btn-login" type="submit">Se connecter</button>
              </div>
            </div>
          </Form>
          <footer class="text-center text-xs text-gray-400 position-absolute bottom-0 p-4">
            En créant un compte ou en vous y connectant, vous acceptez nos conditions générales d'utilisation
            ainsi que notre politique de confidentialité.
            <br />
            Tous droits réservés © {{ yearRange }},
            <a href="https://eryx.ma" target="_blank" class="text-blue-600 hover:underline">Eryx.ma</a>
          </footer>
        </div>

        <!-- Side Image Section -->
        <div class="side-image-section">
          <img src="@/assets/img/image.jpg" alt="Login Image" class="side-image" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Only adding minimal styles for the side image layout */
.login-wrapper {
  min-height: 100vh;
}

.side-image-section {
  flex: 1;
  min-height: 100vh;
}

.side-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.login-content {
  flex: 1;
  padding: 2rem;
}

/* Responsive - stack vertically on mobile */
@media (max-width: 768px) {
  .login-wrapper {
    flex-direction: column;
  }

  .side-image-section {
    flex: none;
    height: 200px;
  }

  .login-content {
    flex: 1;
    min-height: calc(100vh - 200px);
  }
}
</style>
<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import * as Yup from "yup";
import axios from "axios";
import { Form, Field } from "vee-validate";




export default {
  components: {
    Form,
    Field,
  },
  data() {
    return {
      showPassword: false,
      emailError: "",
      passwordError: "",
      startYear: 2023,
      currentYear: new Date().getFullYear(),
      
    };
  },
  computed: {
    yearRange() {
      return this.startYear === this.currentYear
        ? `${this.startYear}`
        : `${this.startYear}–${this.currentYear}`;
    },
    buttonLabel() {
      return this.showPassword ? "Hide" : "Show";
    },
  },
  methods: {
    toggleShow() {
      this.showPassword = !this.showPassword;
    },
  },
  setup() {
    const router = useRouter();

    const schema = Yup.object().shape({
      email: Yup.string()
        .required("Email is required")
        .email("Email is invalid"),
      password: Yup.string()
        .min(6, "Password must be at least 6 characters")
        .required("Password is required"),
    });

    const onSubmit = async (values) => {
      // Clear previous errors
      document.getElementById("email").innerHTML = "";
      document.getElementById("password").innerHTML = "";

      try {
        const response = await axios.post("/login", {
          email: values.email,
          password: values.password,
        });

        // ✅ Successful login — store token and redirect
        if (response.data.access_token) {
          localStorage.setItem("access_token", response.data.access_token);
          let user = {
            username: response.data.user.name,
            role: response.data.role ? "admin" : "user",
            email: response.data.user.email,
          };

          const expiresAt = response.data.expires_at;
          localStorage.setItem("expires_at", expiresAt);
          localStorage.setItem("user", JSON.stringify(user));
          router.push("/dashboard");
        } else {
          document.getElementById("email").innerHTML =
            "Login failed. Unexpected response.";
        }
      } catch (error) {
        const res = error.response;

        // Laravel validation error (422)
        if (res?.status === 422) {
          const errors = res.data.message;
          if (errors) {
            document.getElementById("email").innerHTML = errors.email[0];
          }
        }
        // Authentication failure (401 or 400)
        else if (res?.status === 401 || res?.status === 400) {
          document.getElementById("email").innerHTML =
            res.data.message || "Email ou mot de passe incorrect";
        }
        // Unexpected server error
        else {
          document.getElementById("email").innerHTML =
            "Échec de la connexion. Veuillez réessayer plus tard..";
          console.error("Login error:", error);
        }
      }
    };

    // ✅ Redirect if user is already authenticated
    onMounted(() => {
      const token = localStorage.getItem("access_token");
      if (token) {
        router.push("/dashboard");
      }
    });

    return {
      schema,
      onSubmit,
      checked: ref(false),
    };
  },
};
</script>