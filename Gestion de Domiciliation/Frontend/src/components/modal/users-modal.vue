<template>
  <!-- Add User -->
  <div class="modal fade" id="add-user">
    <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 d-flex justify-content-between custom-modal-header">
              <div class="page-title">
                <h4>Ajouter un utilisateur</h4>
              </div>
              <button
                type="button"
                class="close"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body custom-modal-body">
              <form @submit.prevent="submitForm" novalidate>
                <div class="row">
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Nom</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="user.nom"
                        :class="{ 'is-invalid': validationErrors.nom }"
                        autocomplete="family-name"
                      />
                      <small v-if="validationErrors.nom" class="text-danger">{{
                        validationErrors.nom
                      }}</small>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Prénom</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="user.prenom"
                        :class="{ 'is-invalid': validationErrors.prenom }"
                        autocomplete="given-name"
                      />
                      <small
                        v-if="validationErrors.prenom"
                        class="text-danger"
                        >{{ validationErrors.prenom }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Email</label>
                      <input
                        type="email"
                        class="form-control"
                        v-model="user.email"
                        :class="{ 'is-invalid': validationErrors.email }"
                        autocomplete="email"
                      />
                      <small
                        v-if="validationErrors.email"
                        class="text-danger"
                        >{{ validationErrors.email }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Téléphone</label>
                      <input
                        type="tel"
                        class="form-control"
                        v-model="user.telephone"
                        :class="{ 'is-invalid': validationErrors.telephone }"
                        autocomplete="tel"
                      />
                      <small
                        v-if="validationErrors.telephone"
                        class="text-danger"
                        >{{ validationErrors.telephone }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Adresse</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="user.adresse"
                        :class="{ 'is-invalid': validationErrors.adresse }"
                        autocomplete="street-address"
                      />
                      <small
                        v-if="validationErrors.adresse"
                        class="text-danger"
                        >{{ validationErrors.adresse }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Ville</label>
                      <Multiselect
                        v-model="user.ville"
                        :options="cities.map((city) => city.name)"
                        :searchable="true"
                        placeholder="Choisir une ville"
                        :class="{ 'is-invalid': validationErrors.ville }"
                        class="form-control"
                      />
                      <small
                        v-if="validationErrors.ville"
                        class="text-danger"
                        >{{ validationErrors.ville }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="input-blocks">
                      <label>Mot de passe (optionnel)</label>
                      <div class="pass-group">
                        <input
                          :type="showPassword ? 'text' : 'password'"
                          class="pass-input form-control"
                          v-model="user.password"
                          autocomplete="new-password"
                          :class="{ 'is-invalid': validationErrors.password }"
                        />
                        <span
                          v-if="!validationErrors.password"
                          class="toggle-password"
                          @click="showPassword = !showPassword"
                        >
                          <i
                            :class="{
                              'fas fa-eye': showPassword,
                              'fas fa-eye-slash': !showPassword,
                            }"
                          ></i>
                        </span>
                      </div>
                      <small
                        v-if="validationErrors.password"
                        class="text-danger"
                        >{{ validationErrors.password }}</small
                      >
                      <small v-else class="text-muted">
                        Si aucun mot de passe n'est fourni, "123456789" sera utilisé par défaut
                      </small>
                    </div>
                  </div>
                  <div class="col-lg-12" v-if="user.password">
                    <div class="input-blocks">
                      <label>Confirmer le mot de passe</label>
                      <div class="pass-group">
                        <input
                          :type="showConfirmPassword ? 'text' : 'password'"
                          class="pass-input form-control"
                          v-model="user.password_confirmation"
                          autocomplete="new-password"
                          :class="{
                            'is-invalid': validationErrors.password_confirmation,
                          }"
                        />
                        <span
                          v-if="!validationErrors.password_confirmation"
                          class="toggle-password"
                          @click="showConfirmPassword = !showConfirmPassword"
                        >
                          <i
                            :class="{
                              'fas fa-eye': showConfirmPassword,
                              'fas fa-eye-slash': !showConfirmPassword,
                            }"
                          ></i>
                        </span>
                      </div>
                      <small
                        v-if="validationErrors.password_confirmation"
                        class="text-danger"
                        >{{ validationErrors.password_confirmation }}</small
                      >
                    </div>
                  </div>
                </div>
                <div class="modal-footer-btn">
                  <button
                    type="button"
                    class="btn btn-cancel me-2"
                    data-bs-dismiss="modal"
                  >
                    Annuler
                  </button>
                  <button type="submit" class="btn btn-submit">Ajouter</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Add User -->

  <!-- Edit User -->
  <div class="modal fade" id="edit-user">
    <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 custom-modal-header">
              <div class="page-title">
                <h4>Modifier l'utilisateur</h4>
              </div>
              <button
                type="button"
                class="close"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body custom-modal-body">
              <form @submit.prevent="submitEditForm" novalidate>
                <div class="row">
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Nom</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="editUser.nom"
                        :class="{ 'is-invalid': validationErrorsEdit.nom }"
                        autocomplete="family-name"
                      />
                      <small
                        v-if="validationErrorsEdit.nom"
                        class="text-danger"
                        >{{ validationErrorsEdit.nom }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Prénom</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="editUser.prenom"
                        :class="{ 'is-invalid': validationErrorsEdit.prenom }"
                        autocomplete="given-name"
                      />
                      <small
                        v-if="validationErrorsEdit.prenom"
                        class="text-danger"
                        >{{ validationErrorsEdit.prenom }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Email</label>
                      <input
                        type="email"
                        class="form-control"
                        v-model="editUser.email"
                        :class="{ 'is-invalid': validationErrorsEdit.email }"
                        autocomplete="email"
                      />
                      <small
                        v-if="validationErrorsEdit.email"
                        class="text-danger"
                        >{{ validationErrorsEdit.email }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Téléphone</label>
                      <input
                        type="tel"
                        class="form-control"
                        v-model="editUser.telephone"
                        :class="{
                          'is-invalid': validationErrorsEdit.telephone,
                        }"
                        autocomplete="tel"
                      />
                      <small
                        v-if="validationErrorsEdit.telephone"
                        class="text-danger"
                        >{{ validationErrorsEdit.telephone }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Adresse</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="editUser.adresse"
                        :class="{
                          'is-invalid': validationErrorsEdit.adresse,
                        }"
                        autocomplete="street-address"
                      />
                      <small
                        v-if="validationErrorsEdit.adresse"
                        class="text-danger"
                        >{{ validationErrorsEdit.adresse }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Ville</label>
                      <Multiselect
                        v-model="editUser.ville"
                        :options="cities.map((city) => city.name)"
                        :searchable="true"
                        placeholder="Choisir une ville"
                        :class="{ 'is-invalid': validationErrorsEdit.ville }"
                        class="form-control"
                      />
                      <small
                        v-if="validationErrorsEdit.ville"
                        class="text-danger"
                        >{{ validationErrorsEdit.ville }}</small
                      >
                    </div>
                  </div>
                </div>
                <div class="modal-footer-btn">
                  <button
                    type="button"
                    class="btn btn-cancel me-2"
                    data-bs-dismiss="modal"
                  >
                    Annuler
                  </button>
                  <button type="submit" class="btn btn-submit">Modifier</button>
                </div>
              </form>
              <div class="mt-3">
                <button
                  class="btn btn-danger"
                  @click="deleteUser(editUser.id)"
                  v-if="editUser.id"
                >
                  Supprimer l'utilisateur
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Edit User -->
</template>

<script>
import axios from "axios";
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";
import Swal from "sweetalert2";
import * as Yup from "yup";
import cities from "@/assets/json/cities.json";

// Enhanced validation schemas with comprehensive rules
const phoneRegex = /^(?:(?:\+|00)212|0)[5-7]\d{8}$/;
const phoneRegexMessage =
  "Format de téléphone marocain invalide. Exemples valides:\n" +
  "• 0600000000\n" +
  "• +212600000000\n" +
  "• 00212600000000";

const addUserSchema = Yup.object().shape({
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
    .nullable()
    .transform((value) => (value === "" ? null : value))
    .test('password-validation', (value, context) => {
      if (!value) return true; // Allow empty password (will be set to default)
      
      if (value.length < 8) {
        return context.createError({
          message: 'Le mot de passe doit contenir au moins 8 caractères'
        });
      }
      if (value.length > 50) {
        return context.createError({
          message: 'Le mot de passe ne peut pas dépasser 50 caractères'
        });
      }
      if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(value)) {
        return context.createError({
          message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial'
        });
      }
      return true;
    }),
  password_confirmation: Yup.string()
    .nullable()
    .test('password-confirmation', function(value) {
      const password = this.parent.password;
      if (!password) return true; // If no password, no confirmation needed
      
      if (!value) {
        return this.createError({
          message: 'La confirmation du mot de passe est obligatoire'
        });
      }
      
      if (value !== password) {
        return this.createError({
          message: 'Les mots de passe ne correspondent pas'
        });
      }
      
      return true;
    })
});

const editUserSchema = Yup.object().shape({
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
});

export default {
  components: { Multiselect },
  data() {
    return {
      // User data structures with proper initialization
      user: this.createEmptyUser(),
      editUser: this.createEmptyEditUser(),

      // UI state
      showPassword: false,
      showConfirmPassword: false,

      // Data sources
      cities: cities,

      // Validation state
      validationErrors: {},
      validationErrorsEdit: {},
    };
  },
  methods: {
    // Data structure factories
    createEmptyUser() {
      return {
        nom: "",
        prenom: "",
        email: "",
        telephone: "",
        adresse: "",
        ville: "",
        password: "",
        password_confirmation: "",
      };
    },

    createEmptyEditUser() {
      return {
        id: null,
        nom: "",
        prenom: "",
        email: "",
        telephone: "",
        adresse: "",
        ville: "",
      };
    },

    // Enhanced validation with better error handling
    async validateUser(userData, schema, context = {}) {
      try {
        await schema.validate(userData, {
          abortEarly: false,
          context,
        });
        return {};
      } catch (error) {
        const errors = {};
        if (error.inner && error.inner.length > 0) {
          error.inner.forEach((err) => {
            if (err.path && !errors[err.path]) {
              errors[err.path] = err.message;
            }
          });
        } else if (error.path) {
          errors[error.path] = error.message;
        }
        return errors;
      }
    },

    // Data sanitization
    sanitizeUserData(userData) {
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
    },

    async submitForm() {
      // Clear previous validation errors
      this.validationErrors = {};

      // Sanitize data
      const sanitizedUser = this.sanitizeUserData(this.user);

      // Validate with Yup schema
      this.validationErrors = await this.validateUser(
        sanitizedUser,
        addUserSchema
      );

      if (Object.keys(this.validationErrors).length > 0) {
        // Focus on first field with error
        this.focusFirstErrorField();
        return;
      }

      // Set default password if empty
      if (!sanitizedUser.password || sanitizedUser.password.trim() === "") {
        sanitizedUser.password = "123456789";
        sanitizedUser.password_confirmation = "123456789";
      }

      try {
        // 1. Create user
        const userPayload = {
          name: `${sanitizedUser.nom} ${sanitizedUser.prenom}`.trim(),
          nom: sanitizedUser.nom,
          prenom: sanitizedUser.prenom,
          email: sanitizedUser.email,
          telephone: sanitizedUser.telephone || null,
          adresse: sanitizedUser.adresse || null,
          ville: sanitizedUser.ville,
          password: sanitizedUser.password,
          password_confirmation: sanitizedUser.password_confirmation,
        };

        const userRes = await axios.post("/users", userPayload);

        // 2. Create gestionnaire linked to this user
        const gestionnairePayload = {
          nom: sanitizedUser.nom,
          prenom: sanitizedUser.prenom,
          email: sanitizedUser.email,
          telephone: sanitizedUser.telephone || null,
          adresse: sanitizedUser.adresse || null,
          ville: sanitizedUser.ville,
          user_id: userRes.data.id || userRes.data.user?.id,
        };

        await axios.post("/gestionnaires", gestionnairePayload);

        // Success handling
        this.$emit("user-added");
        this.resetUser();
        this.hideModal("#add-user");

        this.showSuccessMessage("Utilisateur et gestionnaire ajoutés avec succès");
      } catch (error) {
        console.error("Add user error:", error);
        this.handleApiError(error, "Échec de l'ajout de l'utilisateur/gestionnaire");
      }
    },

    async submitEditForm() {
      console.log("submitEditForm called with data:", this.editUser);
      
      // Clear previous validation errors
      this.validationErrorsEdit = {};

      // Validate that we have an ID
      if (!this.editUser.id) {
        this.showErrorMessage("Impossible de modifier l'utilisateur (ID manquant)");
        return;
      }

      // Sanitize data
      const sanitizedEditUser = this.sanitizeUserData(this.editUser);
      console.log("Sanitized data:", sanitizedEditUser);

      // Validate with Yup schema
      this.validationErrorsEdit = await this.validateUser(
        sanitizedEditUser,
        editUserSchema
      );

      if (Object.keys(this.validationErrorsEdit).length > 0) {
        console.log("Validation errors:", this.validationErrorsEdit);
        this.focusFirstErrorField(true);
        return;
      }

      try {
        const payload = {
          name: `${sanitizedEditUser.nom} ${sanitizedEditUser.prenom}`.trim(),
          nom: sanitizedEditUser.nom,
          prenom: sanitizedEditUser.prenom,
          email: sanitizedEditUser.email,
          telephone: sanitizedEditUser.telephone || null,
          adresse: sanitizedEditUser.adresse || null,
          ville: sanitizedEditUser.ville,
        };

        console.log("Sending PUT request to:", `/users/${sanitizedEditUser.id}`);
        console.log("Payload:", payload);

        const response = await axios.put(`/users/${sanitizedEditUser.id}`, payload);
        console.log("Update response:", response);

        // Also update the gestionnaire if it exists
        try {
          const gestionnairePayload = {
            nom: sanitizedEditUser.nom,
            prenom: sanitizedEditUser.prenom,
            email: sanitizedEditUser.email,
            telephone: sanitizedEditUser.telephone || null,
            adresse: sanitizedEditUser.adresse || null,
            ville: sanitizedEditUser.ville,
          };
          
          // Try to update gestionnaire by user_id
          await axios.put(`/gestionnaires/${sanitizedEditUser.id}`, gestionnairePayload);
          console.log("Gestionnaire updated successfully");
        } catch (gestionnaireError) {
          console.warn("Failed to update gestionnaire (might not exist):", gestionnaireError);
          // Don't fail the entire operation if gestionnaire update fails
        }

        // Success handling
        this.$emit("user-edited");
        this.resetEditUser();
        this.hideModal("#edit-user");

        this.showSuccessMessage("Utilisateur modifié avec succès");
      } catch (error) {
        console.error("Edit user error:", error);
        this.handleApiError(error, "Échec de la modification de l'utilisateur");
      }
    },

    async deleteUser(id) {
      if (!id) {
        this.showErrorMessage("Impossible de supprimer cet utilisateur (ID manquant).");
        return;
      }

      const result = await Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action supprimera l'utilisateur définitivement.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Annuler",
        confirmButtonClass: "btn btn-danger",
        cancelButtonClass: "btn btn-secondary",
        buttonsStyling: false,
        reverseButtons: true,
        position: 'top-end',
        showClass: {
          popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp'
        }
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/users/${id}`);

          this.$emit("user-edited");
          this.resetEditUser();
          this.hideModal("#edit-user");

          this.showSuccessMessage("Utilisateur supprimé avec succès");
        } catch (error) {
          this.handleApiError(error, "Échec de la suppression de l'utilisateur");
        }
      }
    },

    // Utility methods
    resetUser() {
      this.user = this.createEmptyUser();
      this.showPassword = false;
      this.showConfirmPassword = false;
      this.validationErrors = {};
    },

    resetEditUser() {
      this.editUser = this.createEmptyEditUser();
      this.validationErrorsEdit = {};
    },

    openEditModal(user) {
      console.log("Opening edit modal with user:", user);
      this.editUser = {
        id: user.id,
        nom: user.nom || "",
        prenom: user.prenom || "",
        email: user.email || "",
        telephone: user.telephone || "",
        adresse: user.adresse || "",
        ville: user.ville || "",
      };
      this.validationErrorsEdit = {};
      console.log("Edit user data set:", this.editUser);
    },

    focusFirstErrorField(isEdit = false) {
      const errors = isEdit ? this.validationErrorsEdit : this.validationErrors;
      const firstErrorField = Object.keys(errors)[0];

      if (firstErrorField) {
        this.$nextTick(() => {
          const modalId = isEdit ? "#edit-user" : "#add-user";
          const element = document.querySelector(
            `${modalId} input[name="${firstErrorField}"], ${modalId} select[name="${firstErrorField}"]`
          );
          if (element) {
            element.focus();
          }
        });
      }
    },

    hideModal(selector) {
      const modal = window.bootstrap
        ? window.bootstrap.Modal.getInstance(document.querySelector(selector))
        : null;
      if (modal) {
        modal.hide();
      } else {
        const el = document.querySelector(selector);
        if (el) {
          el.classList.remove("show");
          el.style.display = "none";
          document.body.classList.remove("modal-open");
          const backdrop = document.querySelector(".modal-backdrop");
          if (backdrop) backdrop.parentNode.removeChild(backdrop);
        }
      }
    },

    showSuccessMessage(message) {
      Swal.fire({
        title: "Succès!",
        text: message,
        icon: "success",
        confirmButtonText: "OK",
        confirmButtonClass: "btn btn-success",
        buttonsStyling: false,
        position: 'top-end',
        toast: true,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        showClass: {
          popup: 'animate__animated animate__slideInRight'
        },
        hideClass: {
          popup: 'animate__animated animate__slideOutRight'
        }
      });
    },

    showErrorMessage(message) {
      Swal.fire({
        title: "Erreur!",
        text: message,
        icon: "error",
        confirmButtonText: "OK",
        confirmButtonClass: "btn btn-danger",
        buttonsStyling: false,
        position: 'top-end',
        toast: true,
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: false,
        showClass: {
          popup: 'animate__animated animate__slideInRight'
        },
        hideClass: {
          popup: 'animate__animated animate__slideOutRight'
        }
      });
    },

    handleApiError(error, defaultMessage) {
      console.error(defaultMessage + ":", error);
      
      let message = defaultMessage;
      if (error.response?.data?.message) {
        message = error.response.data.message;
      } else if (error.response?.data?.error) {
        message = error.response.data.error;
      } else if (error.response?.data?.errors) {
        // Handle Laravel validation errors
        const errors = error.response.data.errors;
        const errorMessages = Object.values(errors).flat();
        message = errorMessages.join(', ');
      } else if (error.message) {
        message = error.message;
      }

      this.showErrorMessage(message);
    },
  },

  // Expose methods to parent component
  expose: ['openEditModal', 'resetUser', 'resetEditUser'],
};
</script>