<template>
  <div class="account-page">
    <!-- Main Wrapper -->
    <div class="account-content">
      <div class="login-wrapper register-wrap bg-img">
        <div class="login-content">
          <Form
            @submit="onSubmit"
            :validation-schema="schema"
            v-slot="{ errors }"
          >
            <div class="login-userset">
              <div class="login-logo logo-normal">
                <img src="@/assets/img/logo.png" alt="img" />
              </div>
              <router-link to="/dashboard" class="login-logo logo-white">
                <img src="@/assets/img/logo.png" alt="" />
              </router-link>
              <div class="login-userheading">
                <h3>Register</h3>
                <h4>Create New Dreamspos Account</h4>
              </div>

              <!-- Row for Nom and Prenom -->
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-login">
                    <label>Nom</label>
                    <div class="form-addons">
                      <Field
                        name="nom"
                        f
                        type="text"
                        class="form-control"
                        placeholder="Nom"
                        value="admin"
                        :class="{ 'is-invalid': errors.nom }"
                        autocomplete="family-name"
                      />
                      <div class="invalid-feedback">{{ errors.nom }}</div>
                      <div class="emailshow text-danger" id="nom"></div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-login">
                    <label>Prénom</label>
                    <div class="form-addons">
                      <Field
                        name="prenom"
                        type="text"
                        class="form-control"
                        placeholder="Prénom"
                        value="admin"
                        :class="{ 'is-invalid': errors.prenom }"
                        autocomplete="given-name"
                      />
                      <div class="invalid-feedback">{{ errors.prenom }}</div>
                      <div class="emailshow text-danger" id="prenom"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-login">
                <label>Email Address</label>
                <div class="form-addons">
                  <Field
                    name="email"
                    type="email"
                    class="form-control"
                    placeholder="Email"
                    value="example@eryx.com"
                    :class="{ 'is-invalid': errors.email }"
                    autocomplete="email"
                  />
                  <div class="invalid-feedback">{{ errors.email }}</div>
                  <div class="emailshow text-danger" id="email"></div>
                  <img
                    v-if="!errors.email"
                    src="@/assets/img/icons/mail.svg"
                    alt="img"
                  />
                </div>
              </div>

              <!-- Row for Telephone and Ville -->
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-login">
                    <label>Téléphone</label>
                    <div class="form-addons">
                      <Field
                        name="telephone"
                        type="tel"
                        class="form-control"
                        value="0608229760"
                        placeholder="Téléphone"
                        :class="{ 'is-invalid': errors.telephone }"
                        autocomplete="tel"
                      />
                      <div class="invalid-feedback">{{ errors.telephone }}</div>
                      <div class="emailshow text-danger" id="telephone"></div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-login">
                    <label>Ville</label>
                    <div class="form-addons">
                      <Field name="ville" v-slot="{ field, value }">
                        <Multiselect
                          v-bind="field"
                          :modelValue="value"
                          @update:modelValue="field.onChange"
                          :options="cities.map((city) => city.name)"
                          :searchable="true"
                          placeholder="Choisir une ville"
                          :class="{ 'is-invalid': errors.ville }"
                          class="form-control"
                        />
                      </Field>
                      <div class="invalid-feedback">{{ errors.ville }}</div>
                      <div class="emailshow text-danger" id="ville"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-login">
                <label>Adresse</label>
                <div class="form-addons">
                  <Field
                    name="adresse"
                    type="text"
                    class="form-control"
                    value="Street 1 , New York"
                    placeholder="Adresse"
                    :class="{ 'is-invalid': errors.adresse }"
                    autocomplete="street-address"
                  />
                  <div class="invalid-feedback">{{ errors.adresse }}</div>
                  <div class="emailshow text-danger" id="adresse"></div>
                </div>
              </div>

              <div class="form-login">
                <label>Password</label>
                <div class="pass-group">
                  <Field
                    name="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="form-control pass-input mt-2"
                    value="Password@123"
                    placeholder="Password"
                    :class="{ 'is-invalid': errors.password }"
                    autocomplete="new-password"
                  />
                  <span
                    v-if="!errors.password"
                    @click="toggleShow"
                    class="toggle-password"
                  >
                    <i
                      :class="{
                        'fas fa-eye': showPassword,
                        'fas fa-eye-slash': !showPassword,
                      }"
                    ></i>
                  </span>
                  <div class="invalid-feedback">{{ errors.password }}</div>
                  <div class="emailshow text-danger" id="password"></div>
                </div>
              </div>

              <div class="form-login">
                <label>Confirm Password</label>
                <div class="pass-group">
                  <Field
                    name="password_confirmation"
                    :type="showPassword1 ? 'text' : 'password'"
                    class="form-control pass-input mt-2"
                    value="Password@123"
                    placeholder="Confirm Password"
                    :class="{ 'is-invalid': errors.password_confirmation }"
                    autocomplete="new-password"
                  />
                  <span
                    v-if="!errors.password_confirmation"
                    @click="toggleShow1"
                    class="toggle-password"
                  >
                    <i
                      :class="{
                        'fas fa-eye': showPassword1,
                        'fas fa-eye-slash': !showPassword1,
                      }"
                    ></i>
                  </span>
                  <div class="invalid-feedback">
                    {{ errors.password_confirmation }}
                  </div>
                  <div
                    class="emailshow text-danger"
                    id="password_confirmation"
                  ></div>
                </div>
              </div>

              <div class="form-login">
                <button type="submit" class="btn btn-login">Sign Up</button>
              </div>
              <div class="signinform">
                <h4>
                  Already have an account ?
                  <router-link to="/" class="hover-a"
                    >Sign In Instead</router-link
                  >
                </h4>
              </div>
              <div class="form-setlogin or-text" style="visibility: hidden">
                <h4>OR</h4>
              </div>
              <div class="form-sociallink">
                <ul class="d-flex" style="visibility: hidden">
                  <li>
                    <a href="javascript:void(0);" class="facebook-logo">
                      <img
                        src="@/assets/img/icons/facebook-logo.svg"
                        alt="Facebook"
                      />
                    </a>
                  </li>
                  <li>
                    <a href="javascript:void(0);">
                      <img src="@/assets/img/icons/google.png" alt="Google" />
                    </a>
                  </li>
                  <li>
                    <a href="javascript:void(0);" class="apple-logo">
                      <img
                        src="@/assets/img/icons/apple-logo.svg"
                        alt="Apple"
                      />
                    </a>
                  </li>
                </ul>
              </div>
              <div
                class="my-4 d-flex justify-content-center align-items-center copyright-text"
              >
                <p>
                  Copyright &copy; {{ new Date().getFullYear() }} DreamsPOS. All
                  rights reserved
                </p>
              </div>
            </div>
          </Form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted } from "vue";
import { Form, Field } from "vee-validate";
import * as Yup from "yup";
import { useRouter } from "vue-router";
import axios from "axios";
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";
import cities from "@/assets/json/cities.json";

export default {
  components: {
    Form,
    Field,
    Multiselect,
  },
  data() {
    return {
      showPassword: false,
      showPassword1: false,
      cities: cities,
    };
  },
  computed: {
    buttonLabel() {
      return this.showPassword ? "Hide" : "Show";
    },
    buttonLabel1() {
      return this.showPassword1 ? "Hide" : "Show";
    },
  },
  methods: {
    toggleShow() {
      this.showPassword = !this.showPassword;
    },
    toggleShow1() {
      this.showPassword1 = !this.showPassword1;
    },
  },
  setup() {
    const router = useRouter();

    // Enhanced validation schema with same rules as modal
    const phoneRegex = /^(?:(?:\+|00)212|0)[5-7]\d{8}$/;
    const phoneRegexMessage =
      "Format de téléphone marocain invalide. Exemples valides:\n" +
      "• 0600000000\n" +
      "• +212600000000\n" +
      "• 00212600000000";

    const schema = Yup.object().shape({
      nom: Yup.string()
        .required("Le nom est obligatoire")
        .min(2, "Le nom doit contenir au moins 2 caractères")
        .max(50, "Le nom ne peut pas dépasser 50 caractères")
        .matches(
          /^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\s-']+$/,
          "Le nom ne doit contenir que des lettres, espaces, tirets et apostrophes"
        ),
      prenom: Yup.string()
        .required("Le prénom est obligatoire")
        .min(2, "Le prénom doit contenir au moins 2 caractères")
        .max(50, "Le prénom ne peut pas dépasser 50 caractères")
        .matches(
          /^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\s-']+$/,
          "Le prénom ne doit contenir que des lettres, espaces, tirets et apostrophes"
        ),
      email: Yup.string()
        .required("L'email est obligatoire")
        .email("Format d'email invalide")
        .max(100, "L'email ne peut pas dépasser 100 caractères")
        .lowercase("L'email doit être en minuscules"),
      ville: Yup.string()
        .required("La ville est obligatoire")
        .max(100, "La ville ne peut pas dépasser 100 caractères"),
      telephone: Yup.string()
        .required("Le numero est obligatoire")
        .transform((value) => (value === "" ? null : value))
        .matches(phoneRegex, phoneRegexMessage),
      adresse: Yup.string()
        .required("L'adresse est obligatoire")
        .transform((value) => (value === "" ? null : value))
        .max(200, "L'adresse ne peut pas dépasser 200 caractères"),
      password: Yup.string()
        .required("Le mot de passe est obligatoire")
        .min(8, "Le mot de passe doit contenir au moins 8 caractères")
        .max(50, "Le mot de passe ne peut pas dépasser 50 caractères")
        .matches(
          /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
          "Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial"
        ),
      password_confirmation: Yup.string()
        .required("La confirmation du mot de passe est obligatoire")
        .oneOf(
          [Yup.ref("password"), null],
          "Les mots de passe ne correspondent pas"
        ),
    });

    // Data sanitization function
    const sanitizeUserData = (userData) => {
      const sanitized = { ...userData };

      // Trim whitespace from string fields
      Object.keys(sanitized).forEach((key) => {
        if (typeof sanitized[key] === "string") {
          sanitized[key] = sanitized[key].trim();
        }
      });

      // Convert email to lowercase
      if (sanitized.email) {
        sanitized.email = sanitized.email.toLowerCase();
      }

      // Format phone number
      if (sanitized.telephone) {
        sanitized.telephone = sanitized.telephone.replace(/\s/g, "");
      }

      return sanitized;
    };

    // Clear error messages helper
    const clearErrorMessages = () => {
      const errorFields = [
        "nom",
        "prenom",
        "email",
        "telephone",
        "ville",
        "adresse",
        "password",
        "password_confirmation",
      ];
      errorFields.forEach((field) => {
        const element = document.getElementById(field);
        if (element) {
          element.innerHTML = "";
        }
      });
    };

    // Display API errors helper
    const displayApiErrors = (errors) => {
      Object.keys(errors).forEach((field) => {
        const element = document.getElementById(field);
        if (element && errors[field]) {
          element.innerHTML = Array.isArray(errors[field])
            ? errors[field][0]
            : errors[field];
        }
      });
    };

    const onSubmit = async (values) => {
      clearErrorMessages();

      // Sanitize the form data
      const sanitizedData = sanitizeUserData(values);

      try {
        // 1. Create user via custom /register endpoint
        const userPayload = {
          name: `${sanitizedData.nom} ${sanitizedData.prenom}`.trim(),
          nom: sanitizedData.nom,
          prenom: sanitizedData.prenom,
          email: sanitizedData.email,
          telephone: sanitizedData.telephone || null,
          adresse: sanitizedData.adresse || null,
          ville: sanitizedData.ville,
          password: sanitizedData.password,
          password_confirmation: sanitizedData.password_confirmation,
          is_admin: false, 
        };

        const registerResponse = await axios.post("/register", userPayload);

        // 2. Extract token and user
        const token = registerResponse.data.access_token;
        const user = registerResponse.data.user;

        // 3. Set Authorization header temporarily for the next request
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        // 4. Create the gestionnaire
        const gestionnairePayload = {
          nom: sanitizedData.nom,
          prenom: sanitizedData.prenom,
          email: sanitizedData.email,
          telephone: sanitizedData.telephone || null,
          adresse: sanitizedData.adresse || null,
          ville: sanitizedData.ville,
          user_id: user.id,
        };

        await axios.post("/gestionnaires", gestionnairePayload);

        // 5. Optionally store token for future use
        localStorage.setItem("access_token", token);

        // Success - show message and redirect
        alert(
          "Inscription réussie! Utilisateur et gestionnaire créés avec succès. Veuillez vous connecter."
        );
        router.push("/");
      } catch (error) {
        console.error("Registration Error:", error);
        const res = error.response;

        if (res?.status === 422) {
          // Handle validation errors
          const errors = res.data.errors || {};
          displayApiErrors(errors);
        } else if (res?.data?.message) {
          alert(res.data.message);
        } else if (res?.data?.error) {
          alert(res.data.error);
        } else {
          console.error("Unexpected Error:", res?.data || error.message);
          alert("Une erreur inattendue s'est produite lors de l'inscription.");
        }
      }
    };

    // ✅ Redirect if already authenticated
    onMounted(() => {
      const token = localStorage.getItem("access_token");
      if (token) {
        router.push("/dashboard");
      }
    });

    return {
      schema,
      onSubmit,
    };
  },
};
</script>
