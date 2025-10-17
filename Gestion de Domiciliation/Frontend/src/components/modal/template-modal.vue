<template>
  <!-- Add Template -->
  <div class="modal fade" id="add-template">
    <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 d-flex justify-content-between custom-modal-header">
              <div class="page-title">
                <h4>Ajouter un template</h4>
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
                      <label>Nom du template</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="template.name"
                        :class="{ 'is-invalid': validationErrors.name }"
                        placeholder="Ex: Contrat de travail"
                      />
                      <small v-if="validationErrors.name" class="text-danger">{{
                        validationErrors.name
                      }}</small>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Version</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="template.version"
                        :class="{ 'is-invalid': validationErrors.version }"
                        placeholder="Ex: 1.0"
                      />
                      <small
                        v-if="validationErrors.version"
                        class="text-danger"
                        >{{ validationErrors.version }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="input-blocks">
                      <label>Description</label>
                      <textarea
                        class="form-control"
                        v-model="template.description"
                        :class="{ 'is-invalid': validationErrors.description }"
                        rows="3"
                        placeholder="Description du template..."
                      ></textarea>
                      <small
                        v-if="validationErrors.description"
                        class="text-danger"
                        >{{ validationErrors.description }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="input-blocks">
                      <label>Contenu HTML</label>
                      <textarea
                        class="form-control"
                        v-model="template.content"
                        :class="{ 'is-invalid': validationErrors.content }"
                        rows="8"
                        placeholder="Contenu HTML du template..."
                      ></textarea>
                      <small
                        v-if="validationErrors.content"
                        class="text-danger"
                        >{{ validationErrors.content }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Variables (JSON)</label>
                      <textarea
                        class="form-control"
                        v-model="template.variables"
                        :class="{ 'is-invalid': validationErrors.variables }"
                        rows="4"
                        placeholder='{"nom": "string", "date": "date"}'
                      ></textarea>
                      <small
                        v-if="validationErrors.variables"
                        class="text-danger"
                        >{{ validationErrors.variables }}</small
                      >
                      <small class="text-muted">
                        Format JSON pour définir les variables du template
                      </small>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Statut</label>
                      <select
                        class="form-control"
                        v-model="template.is_active"
                        :class="{ 'is-invalid': validationErrors.is_active }"
                      >
                        <option :value="true">Actif</option>
                        <option :value="false">Inactif</option>
                      </select>
                      <small
                        v-if="validationErrors.is_active"
                        class="text-danger"
                        >{{ validationErrors.is_active }}</small
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
  <!-- /Add Template -->

  <!-- Edit Template -->
  <div class="modal fade" id="edit-template">
    <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 d-flex justify-content-between custom-modal-header">
              <div class="page-title">
                <h4>Modifier le template</h4>
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
                      <label>Nom du template</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="editTemplate.name"
                        :class="{ 'is-invalid': validationErrorsEdit.name }"
                        placeholder="Ex: Contrat de travail"
                      />
                      <small
                        v-if="validationErrorsEdit.name"
                        class="text-danger"
                        >{{ validationErrorsEdit.name }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Version</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="editTemplate.version"
                        :class="{ 'is-invalid': validationErrorsEdit.version }"
                        placeholder="Ex: 1.0"
                      />
                      <small
                        v-if="validationErrorsEdit.version"
                        class="text-danger"
                        >{{ validationErrorsEdit.version }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="input-blocks">
                      <label>Description</label>
                      <textarea
                        class="form-control"
                        v-model="editTemplate.description"
                        :class="{ 'is-invalid': validationErrorsEdit.description }"
                        rows="3"
                        placeholder="Description du template..."
                      ></textarea>
                      <small
                        v-if="validationErrorsEdit.description"
                        class="text-danger"
                        >{{ validationErrorsEdit.description }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="input-blocks">
                      <label>Contenu HTML</label>
                      <textarea
                        class="form-control"
                        v-model="editTemplate.content"
                        :class="{ 'is-invalid': validationErrorsEdit.content }"
                        rows="8"
                        placeholder="Contenu HTML du template..."
                      ></textarea>
                      <small
                        v-if="validationErrorsEdit.content"
                        class="text-danger"
                        >{{ validationErrorsEdit.content }}</small
                      >
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Variables (JSON)</label>
                      <textarea
                        class="form-control"
                        v-model="editTemplate.variables"
                        :class="{ 'is-invalid': validationErrorsEdit.variables }"
                        rows="4"
                        placeholder='{"nom": "string", "date": "date"}'
                      ></textarea>
                      <small
                        v-if="validationErrorsEdit.variables"
                        class="text-danger"
                        >{{ validationErrorsEdit.variables }}</small
                      >
                      <small class="text-muted">
                        Format JSON pour définir les variables du template
                      </small>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Statut</label>
                      <select
                        class="form-control"
                        v-model="editTemplate.is_active"
                        :class="{ 'is-invalid': validationErrorsEdit.is_active }"
                      >
                        <option :value="true">Actif</option>
                        <option :value="false">Inactif</option>
                      </select>
                      <small
                        v-if="validationErrorsEdit.is_active"
                        class="text-danger"
                        >{{ validationErrorsEdit.is_active }}</small
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
                  @click="deleteTemplate(editTemplate.id)"
                  v-if="editTemplate.id"
                >
                  Supprimer le template
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Edit Template -->
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import * as Yup from "yup";

// Validation schemas for templates
const addTemplateSchema = Yup.object().shape({
  name: Yup.string()
    .required("Le nom du template est obligatoire")
    .min(2, "Le nom doit contenir au moins 2 caractères")
    .max(255, "Le nom ne peut pas dépasser 255 caractères"),
  description: Yup.string()
    .nullable()
    .max(1000, "La description ne peut pas dépasser 1000 caractères"),
  content: Yup.string()
    .required("Le contenu est obligatoire")
    .min(10, "Le contenu doit contenir au moins 10 caractères"),
  variables: Yup.string()
    .nullable()
    .test('is-json', 'Le format JSON des variables est invalide', function(value) {
      if (!value || value.trim() === '') return true;
      try {
        JSON.parse(value);
        return true;
      } catch (error) {
        return false;
      }
    }),
  version: Yup.string()
    .nullable()
    .max(10, "La version ne peut pas dépasser 10 caractères")
    .matches(/^[0-9]+\.[0-9]+(\.[0-9]+)?$/, "Format de version invalide (ex: 1.0 ou 1.2.3)"),
  is_active: Yup.boolean()
    .required("Le statut est obligatoire")
});

const editTemplateSchema = addTemplateSchema;

export default {
  data() {
    return {
      // Template data structures
      template: this.createEmptyTemplate(),
      editTemplate: this.createEmptyEditTemplate(),

      // Validation state
      validationErrors: {},
      validationErrorsEdit: {},
    };
  },
  methods: {
    // Data structure factories
    createEmptyTemplate() {
      return {
        name: "",
        description: "",
        content: "",
        variables: "",
        is_active: true,
        version: "",
      };
    },

    createEmptyEditTemplate() {
      return {
        id: null,
        name: "",
        description: "",
        content: "",
        variables: "",
        is_active: true,
        version: "",
      };
    },

    // Validation
    async validateTemplate(templateData, schema) {
      try {
        await schema.validate(templateData, {
          abortEarly: false,
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
    sanitizeTemplateData(templateData) {
      const sanitized = { ...templateData };

      // Trim whitespace from string fields
      Object.keys(sanitized).forEach((key) => {
        if (typeof sanitized[key] === "string") {
          sanitized[key] = sanitized[key].trim();
        }
      });

      // Parse variables JSON if provided
      if (sanitized.variables && sanitized.variables.trim() !== '') {
        try {
          // Validate JSON format
          JSON.parse(sanitized.variables);
        } catch (error) {
          // Keep as string for validation to catch the error
        }
      } else {
        sanitized.variables = null;
      }

      return sanitized;
    },

    async submitForm() {
      // Clear previous validation errors
      this.validationErrors = {};

      // Sanitize data
      const sanitizedTemplate = this.sanitizeTemplateData(this.template);

      // Validate with Yup schema
      this.validationErrors = await this.validateTemplate(
        sanitizedTemplate,
        addTemplateSchema
      );

      if (Object.keys(this.validationErrors).length > 0) {
        this.focusFirstErrorField();
        return;
      }

      try {
        const payload = {
          name: sanitizedTemplate.name,
          description: sanitizedTemplate.description || null,
          content: sanitizedTemplate.content,
          variables: sanitizedTemplate.variables ? JSON.parse(sanitizedTemplate.variables) : null,
          is_active: sanitizedTemplate.is_active,
          version: sanitizedTemplate.version || null,
        };

        const response = await axios.post("/templates", payload);

        // Success handling
        this.$emit("template-added");
        this.resetTemplate();
        this.hideModal("#add-template");

        this.showSuccessMessage("Template ajouté avec succès");
      } catch (error) {
        this.handleApiError(error, "Échec de l'ajout du template");
      }
    },

    async submitEditForm() {
      // Clear previous validation errors
      this.validationErrorsEdit = {};

      // Validate that we have an ID
      if (!this.editTemplate.id) {
        this.showErrorMessage("Impossible de modifier le template (ID manquant)");
        return;
      }

      // Sanitize data
      const sanitizedEditTemplate = this.sanitizeTemplateData(this.editTemplate);

      // Validate with Yup schema
      this.validationErrorsEdit = await this.validateTemplate(
        sanitizedEditTemplate,
        editTemplateSchema
      );

      if (Object.keys(this.validationErrorsEdit).length > 0) {
        this.focusFirstErrorField(true);
        return;
      }

      try {
        const payload = {
          name: sanitizedEditTemplate.name,
          description: sanitizedEditTemplate.description || null,
          content: sanitizedEditTemplate.content,
          variables: sanitizedEditTemplate.variables ? JSON.parse(sanitizedEditTemplate.variables) : null,
          is_active: sanitizedEditTemplate.is_active,
          version: sanitizedEditTemplate.version || null,
        };

        const response = await axios.put(`/templates/${sanitizedEditTemplate.id}`, payload);

        // Success handling
        this.$emit("template-edited");
        this.resetEditTemplate();
        this.hideModal("#edit-template");

        this.showSuccessMessage("Template modifié avec succès");
      } catch (error) {
        this.handleApiError(error, "Échec de la modification du template");
      }
    },

    async deleteTemplate(id) {
      if (!id) {
        this.showErrorMessage("Impossible de supprimer ce template (ID manquant).");
        return;
      }

      const result = await Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action supprimera le template définitivement.",
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
          await axios.delete(`/templates/${id}`);

          this.$emit("template-edited");
          this.resetEditTemplate();
          this.hideModal("#edit-template");

          this.showSuccessMessage("Template supprimé avec succès");
        } catch (error) {
          this.handleApiError(error, "Échec de la suppression du template");
        }
      }
    },

    // Utility methods
    resetTemplate() {
      this.template = this.createEmptyTemplate();
      this.validationErrors = {};
    },

    resetEditTemplate() {
      this.editTemplate = this.createEmptyEditTemplate();
      this.validationErrorsEdit = {};
    },

    openEditModal(template) {
      this.editTemplate = {
        id: template.id,
        name: template.name || "",
        description: template.description || "",
        content: template.content || "",
        variables: template.variables ? JSON.stringify(template.variables, null, 2) : "",
        is_active: template.is_active !== undefined ? template.is_active : true,
        version: template.version || "",
      };
      this.validationErrorsEdit = {};
    },

    focusFirstErrorField(isEdit = false) {
      const errors = isEdit ? this.validationErrorsEdit : this.validationErrors;
      const firstErrorField = Object.keys(errors)[0];

      if (firstErrorField) {
        this.$nextTick(() => {
          const modalId = isEdit ? "#edit-template" : "#add-template";
          const element = document.querySelector(
            `${modalId} input[name="${firstErrorField}"], ${modalId} select[name="${firstErrorField}"], ${modalId} textarea[name="${firstErrorField}"]`
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
  expose: ['openEditModal', 'resetTemplate', 'resetEditTemplate'],
};
</script>