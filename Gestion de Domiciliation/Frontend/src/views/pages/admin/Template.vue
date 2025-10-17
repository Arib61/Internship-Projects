<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <!-- Enhanced Header Section - Style moderne -->
      <div class="page-header modern-header" style="background-color: #ABC270;">
        <div class="header-content">
          <div class="page-title-section">
            <div class="title-wrapper">
              <div class="icon-wrapper">
                <i class="fas fa-file-alt text-primary"></i>
              </div>
              <div class="title-content">
                <h4 class="page-title">Gestion des Templates des Fichiers</h4>
                <p class="page-subtitle">Gérez vos modèles de documents</p>
              </div>
            </div>
          </div>

          <div class="header-actions">
            <div class="export-actions">
              <button class="action-btn export-btn" data-bs-toggle="tooltip" title="Exporter en PDF">
                <i class="fas fa-file-pdf"></i>
              </button>
              <button class="action-btn export-btn" data-bs-toggle="tooltip" title="Exporter en Excel">
                <i class="fas fa-file-excel"></i>
              </button>
              <button class="action-btn export-btn" data-bs-toggle="tooltip" title="Imprimer">
                <i class="fas fa-print"></i>
              </button>
            </div>
            <div class="page-btn">
              <button @click="openAddModal" class="btn btn-added">
                <vue-feather type="plus-circle" class="me-2"></vue-feather>
                Nouveau Template
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Filter Card -->
      <div class="card filter-card">
        <div class="card-header">
          <h6 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtres et Recherche
          </h6>
          <div class="d-flex align-items-center gap-3">
            <div class="total-templates-badge">
              <i class="fas fa-file-text me-1"></i>
              <span class="fw-bold">{{ pagination.total || 0 }}</span> Templates
            </div>
            <button class="btn btn-sm btn-outline-secondary" @click="clearFilters">
              <i class="fas fa-times me-1"></i>
              Effacer
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="filter-grid">
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-search me-1"></i>
                Recherche générale
              </label>
              <div class="search-input-wrapper">
                <input type="text" class="form-control search-input" placeholder="Rechercher un template..." 
                  v-model="searchQuery" @input="debounceSearch" />
                <i class="fas fa-search search-icon"></i>
              </div>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-file-alt me-1"></i>
                Type de Document
              </label>
              <select v-model="filters.document_type" @change="fetchTemplates" class="form-select">
                <option value="">Tous les types de documents</option>
                <option value="CONTRAT_DOMICILIATION">Contrat de Domiciliation</option>
                <option value="ATTESTATION_DOMICILIATION">Attestation de Domiciliation</option>
                <option value="FACTURE">Facture</option>
                <option value="RECU_PAIEMENT">Reçu de Paiement</option>
                <option value="CERTIFICAT">Certificat</option>
                <option value="AUTRE">Autre</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-code me-1"></i>
                Type de Template
              </label>
              <select v-model="filters.type" @change="fetchTemplates" class="form-select">
                <option value="">Tous les types de templates</option>
                <option value="blade">Blade</option>
                <option value="word">Word</option>
                <option value="pdf">PDF</option>
                <option value="html">HTML</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-toggle-on me-1"></i>
                Statut
              </label>
              <select v-model="filters.is_active" @change="fetchTemplates" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="1">Actif</option>
                <option value="0">Inactif</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
          <span class="sr-only">Chargement...</span>
        </div>
        <p class="mt-3 text-muted">Chargement des templates...</p>
      </div>

      <!-- Enhanced Templates Grid -->
      <div v-else class="templates-grid">
        <div class="row g-4">
          <div
            class="col-xxl-3 col-xl-4 col-lg-6 col-md-6"
            v-for="template in templates"
            :key="template.id"
          >
            <div class="card template-card h-100">
              <div class="card-header template-card-header">
                <div class="d-flex align-items-center">
                  <label class="checkboxs me-2">
                    <input type="checkbox" />
                    <span class="checkmarks"></span>
                  </label>
                  <span :class="getStatusClass(template.is_active)" class="status-badge">
                    {{ getStatusText(template.is_active) }}
                  </span>
                </div>
                <div class="dropdown">
                  <a
                    href="javascript:void(0);"
                    class="action-icon-modern"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                  >
                    <vue-feather type="more-vertical" size="18"></vue-feather>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-modern">
                    <li>
                      <button @click="openEditModal(template)" class="dropdown-item">
                        <vue-feather type="edit" class="dropdown-icon"></vue-feather>
                        Modifier
                      </button>
                    </li>
                    <li>
                      <button @click="openContentEditor(template)" class="dropdown-item">
                        <vue-feather type="edit-3" class="dropdown-icon"></vue-feather>
                        Éditer Contenu
                      </button>
                    </li>
                    <li>
                      <button @click="toggleTemplateStatus(template)" class="dropdown-item">
                        <vue-feather 
                          :type="template.is_active ? 'eye-off' : 'eye'" 
                          class="dropdown-icon"
                        ></vue-feather>
                        {{ template.is_active ? 'Désactiver' : 'Activer' }}
                      </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <button @click="confirmDeleteTemplate(template.id)" class="dropdown-item text-danger">
                        <vue-feather type="trash-2" class="dropdown-icon"></vue-feather>
                        Supprimer
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="card-body template-card-body d-flex flex-column">
                <div class="text-center mb-3">
                  <div class="template-icon-wrapper">
                    <vue-feather type="file-text" class="template-icon" size="28"></vue-feather>
                  </div>
                </div>

                <div class="text-center mb-3">
                  <small class="template-version">{{ template.version || "v1.0" }}</small>
                  <h5 class="template-title">
                    {{ truncateText(template.name, 25) }}
                  </h5>
                  <p class="template-description">
                    {{ truncateText(template.description || "Aucune description", 60) }}
                  </p>
                  <div class="template-badges">
                    <span class="badge template-type-badge">{{ template.type }}</span>
                    <span class="badge template-doc-badge">{{ getDocumentTypeLabel(template.document_type) }}</span>
                  </div>
                </div>

                <div class="template-stats">
                  <div class="row text-center mb-3">
                    <div class="col">
                      <div class="stat-item">
                        <span class="stat-value">{{ getVariableCount(template.variables) }}</span>
                        <small class="stat-label">Variables</small>
                      </div>
                    </div>
                    <div class="col">
                      <div class="stat-item">
                        <span class="stat-value">{{ template.creator?.name || 'N/A' }}</span>
                        <small class="stat-label">Créé par</small>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="template-actions mt-auto">
                  <div class="d-flex gap-2">
                    <button @click="viewTemplate(template)" class="btn btn-template-view flex-fill">
                      <vue-feather type="eye" size="16" class="me-1"></vue-feather>
                      <span>Voir</span>
                    </button>
                    <div class="dropdown flex-fill">
                      <button
                        class="btn btn-template-download dropdown-toggle w-100"
                        type="button"
                        :id="`downloadDropdown${template.id}`"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                      >
                        <vue-feather type="download" size="16" class="me-1"></vue-feather>
                        <span>Télécharger</span>
                      </button>
                      <ul class="dropdown-menu" :aria-labelledby="`downloadDropdown${template.id}`">
                        <li>
                          <button @click="downloadTemplate(template, 'word')" class="dropdown-item">
                            <vue-feather type="file-text" class="dropdown-icon"></vue-feather>
                            Word (.docx)
                          </button>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Pagination -->
      <div class="pagination-wrapper" v-if="pagination.total > 0">
        <div class="pagination-info">
          <div class="entries-selector">
            <label class="entries-label">Afficher :</label>
            <select v-model="pagination.per_page" @change="handleItemsPerPageChange" class="form-select entries-select">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="20">20</option>
              <option value="25">25</option>
            </select>
            <span class="entries-text">entrées par page</span>
          </div>

          <div class="records-info">
            <span class="records-text">
              Affichage de <strong>{{ pagination.from || 0 }}</strong> à <strong>{{ pagination.to || 0 }}</strong>
              sur <strong>{{ pagination.total }}</strong> templates
            </span>
          </div>
        </div>

        <div class="pagination-controls">
          <button class="pagination-btn first-btn" :disabled="pagination.current_page === 1" @click="goToPage(1)">
            <i class="fas fa-angle-double-left"></i>
          </button>
          <button class="pagination-btn prev-btn" :disabled="pagination.current_page === 1" @click="previousPage">
            <i class="fas fa-angle-left"></i>
          </button>

          <div class="page-selector">
            <span class="page-text">Page :</span>
            <select v-model="pagination.current_page" class="form-select page-select" @change="goToPage(pagination.current_page)">
              <option v-for="page in visiblePages" :key="page" :value="page">{{ page }}</option>
            </select>
            <span class="page-total">sur {{ pagination.last_page }}</span>
          </div>

          <button class="pagination-btn next-btn" :disabled="pagination.current_page === pagination.last_page" @click="nextPage">
            <i class="fas fa-angle-right"></i>
          </button>
          <button class="pagination-btn last-btn" :disabled="pagination.current_page === pagination.last_page" @click="goToPage(pagination.last_page)">
            <i class="fas fa-angle-double-right"></i>
          </button>
        </div>
      </div>

      <!-- Add Template Modal -->
      <div class="modal fade" id="add-template">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Nouveau Template</h4>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="saveTemplate">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Nom du Template <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="templateForm.name"
                        required
                        placeholder="Nom du template"
                      />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Version <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="templateForm.version"
                        required
                        placeholder="v1.0"
                      />
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Type de Template <span class="text-danger">*</span></label>
                      <select class="form-select" v-model="templateForm.type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="blade">Blade</option>
                        <option value="word">Word</option>
                        <option value="pdf">PDF</option>
                        <option value="html">HTML</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Type de Document <span class="text-danger">*</span></label>
                      <select class="form-select" v-model="templateForm.document_type" required>
                        <option value="">Sélectionner un type de document</option>
                        <option value="CONTRAT_DOMICILIATION">Contrat de Domiciliation</option>
                        <option value="ATTESTATION_DOMICILIATION">Attestation de Domiciliation</option>
                        <option value="FACTURE">Facture</option>
                        <option value="RECU_PAIEMENT">Reçu de Paiement</option>
                        <option value="CERTIFICAT">Certificat</option>
                        <option value="AUTRE">Autre</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea
                    class="form-control"
                    v-model="templateForm.description"
                    rows="3"
                    placeholder="Description du template"
                  ></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Contenu <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="templateForm.content"
                    rows="8"
                    required
                    placeholder="Contenu du template"
                  ></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Variables (JSON) <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="templateForm.variables"
                    rows="4"
                    required
                    placeholder='{"variable1": "description", "variable2": "description"}'
                  ></textarea>
                  <small class="text-muted">Format JSON requis</small>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      v-model="templateForm.is_active"
                      id="is_active_add"
                    />
                    <label class="form-check-label" for="is_active_add">
                      Template actif
                    </label>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Annuler
              </button>
              <button type="button" class="btn btn-primary" @click="saveTemplate">
                Créer Template
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Template Modal -->
      <div class="modal fade" id="edit-template">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Modifier Template</h4>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="updateTemplate">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Nom du Template <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="editTemplateForm.name"
                        required
                        placeholder="Nom du template"
                      />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Version <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="editTemplateForm.version"
                        required
                        placeholder="v1.0"
                      />
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Type de Template <span class="text-danger">*</span></label>
                      <select class="form-select" v-model="editTemplateForm.type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="blade">Blade</option>
                        <option value="word">Word</option>
                        <option value="pdf">PDF</option>
                        <option value="html">HTML</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Type de Document <span class="text-danger">*</span></label>
                      <select class="form-select" v-model="editTemplateForm.document_type" required>
                        <option value="">Sélectionner un type de document</option>
                        <option value="CONTRAT_DOMICILIATION">Contrat de Domiciliation</option>
                        <option value="ATTESTATION_DOMICILIATION">Attestation de Domiciliation</option>
                        <option value="FACTURE">Facture</option>
                        <option value="RECU_PAIEMENT">Reçu de Paiement</option>
                        <option value="CERTIFICAT">Certificat</option>
                        <option value="AUTRE">Autre</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea
                    class="form-control"
                    v-model="editTemplateForm.description"
                    rows="3"
                    placeholder="Description du template"
                  ></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Contenu <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="editTemplateForm.content"
                    rows="8"
                    required
                    placeholder="Contenu du template"
                  ></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Variables (JSON) <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="editTemplateForm.variables"
                    rows="4"
                    required
                    placeholder='{"variable1": "description", "variable2": "description"}'
                  ></textarea>
                  <small class="text-muted">Format JSON requis</small>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      v-model="editTemplateForm.is_active"
                      id="is_active_edit"
                    />
                    <label class="form-check-label" for="is_active_edit">
                      Template actif
                    </label>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Annuler
              </button>
              <button type="button" class="btn btn-primary" @click="updateTemplate">
                Mettre à jour
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- View Template Modal -->
      <div class="modal fade" id="view-template">
        <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
              <h4 class="modal-title">{{ viewingTemplate.name }}</h4>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <h6>Informations du Template</h6>
                  <p><strong>Nom:</strong> {{ viewingTemplate.name }}</p>
                  <p><strong>Type:</strong> {{ viewingTemplate.type }}</p>
                  <p><strong>Type de document:</strong> {{ getDocumentTypeLabel(viewingTemplate.document_type) }}</p>
                  <p><strong>Version:</strong> {{ viewingTemplate.version || "N/A" }}</p>
                  <p><strong>Description:</strong> {{ viewingTemplate.description || "Aucune description" }}</p>
                  <p><strong>Statut:</strong> {{ getStatusText(viewingTemplate.is_active) }}</p>
                  <p><strong>Variables:</strong> {{ getVariableCount(viewingTemplate.variables) }}</p>
                  <p><strong>Créé par:</strong> {{ viewingTemplate.creator?.name || 'N/A' }}</p>
                  <p><strong>Créé le:</strong> {{ formatDate(viewingTemplate.created_at) }}</p>
                </div>
                <div class="col-md-6">
                  <h6>Aperçu du Contenu</h6>
                  <div class="template-preview-container">
                    <div
                      class="template-preview-content"
                      :class="getContentDirectionClass(viewingTemplate.content)"
                      v-html="sanitizeContent(viewingTemplate.content)"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Fermer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Editor Modal -->
      <div class="modal fade" id="content-editor-modal">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
          <div class="modal-content editor-modal-content">
            <div class="modal-header">
              <h4 class="modal-title">
                Éditeur de Contenu - {{ editingTemplate.name }}
              </h4>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body editor-modal-body">
              <div class="editor-layout">
                <div class="editor-section">
                  <div class="editor-header">
                    <label class="form-label mb-0">Contenu du Template</label>
                    <div class="direction-controls">
                      <div class="btn-group" role="group">
                        <button
                          type="button"
                          class="btn btn-sm"
                          :class="contentDirection === 'ltr' ? 'btn-primary' : 'btn-outline-primary'"
                          @click="setContentDirection('ltr')"
                          title="Direction Gauche à Droite"
                        >
                          <vue-feather type="align-left" size="14"></vue-feather>
                          LTR
                        </button>
                        <button
                          type="button"
                          class="btn btn-sm"
                          :class="contentDirection === 'rtl' ? 'btn-primary' : 'btn-outline-primary'"
                          @click="setContentDirection('rtl')"
                          title="Direction Droite à Gauche"
                        >
                          <vue-feather type="align-right" size="14"></vue-feather>
                          RTL
                        </button>
                        <button
                          type="button"
                          class="btn btn-sm"
                          :class="contentDirection === 'auto' ? 'btn-primary' : 'btn-outline-primary'"
                          @click="setContentDirection('auto')"
                          title="Direction Automatique"
                        >
                          <vue-feather type="shuffle" size="14"></vue-feather>
                          AUTO
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="editor-container">
                    <textarea
                      ref="tinymceEditor"
                      id="tinymce-editor"
                      v-model="editingTemplate.content"
                    ></textarea>
                  </div>
                </div>

                <div class="preview-section">
                  <label class="form-label">Aperçu</label>
                  <div
                    class="preview-container"
                    :class="getContentDirectionClass(editingTemplate.content)"
                    v-html="sanitizeContent(editingTemplate.content)"
                  ></div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Annuler
              </button>
              <button type="button" class="btn btn-primary" @click="saveContent">
                Sauvegarder
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import { Document, Packer, Paragraph, TextRun, HeadingLevel } from "docx";

export default {
  name: "GestionTemplates",
  data() {
    return {
      searchQuery: "",
      templates: [],
      loading: true,
      pagination: {
        current_page: 1,
        per_page: 15,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
      },
      filters: {
        document_type: '',
        type: '',
        is_active: ''
      },
      templateForm: {
        name: '',
        description: '',
        content: '',
        type: '',
        document_type: '',
        variables: '{}',
        version: 'v1.0',
        is_active: true
      },
      editTemplateForm: {
        id: null,
        name: '',
        description: '',
        content: '',
        type: '',
        document_type: '',
        variables: '{}',
        version: 'v1.0',
        is_active: true
      },
      viewingTemplate: {},
      editingTemplate: {},
      contentDirection: "auto",
      tinymceEditor: null,
      tinymceInitialized: false,
      searchTimeout: null,
    };
  },
  computed: {
    visiblePages() {
      const pages = [];
      const start = Math.max(1, this.pagination.current_page - 2);
      const end = Math.min(this.pagination.last_page, this.pagination.current_page + 2);

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    },
  },
  methods: {
    // ==================== Filter & Pagination ====================
    clearFilters() {
      this.searchQuery = "";
      this.filters = {
        document_type: '',
        type: '',
        is_active: ''
      };
      this.pagination.current_page = 1;
      this.fetchTemplates();
    },

    async fetchTemplates(page = 1) {
      try {
        this.loading = true;
        const params = {
          page: page,
          per_page: this.pagination.per_page,
          search: this.searchQuery,
          ...this.filters
        };

        // Remove empty filters
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] === null || params[key] === undefined) {
            delete params[key];
          }
        });

        const response = await axios.get("/templates", { params });
        
        if (response.data.success) {
          this.templates = response.data.data.data || [];
          this.pagination = {
            current_page: response.data.data.current_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total,
            last_page: response.data.data.last_page,
            from: response.data.data.from,
            to: response.data.data.to
          };
        }
      } catch (error) {
        console.error("Erreur lors du chargement des templates:", error);
        this.showErrorMessage("Erreur lors du chargement des templates");
        this.templates = [];
      } finally {
        this.loading = false;
      }
    },

    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.pagination.current_page = 1;
        this.fetchTemplates();
      }, 500);
    },

    async saveTemplate() {
      try {
        // Validate JSON format for variables
        try {
          JSON.parse(this.templateForm.variables);
        } catch (e) {
          this.showErrorMessage("Le format JSON des variables est invalide");
          return;
        }

        console.log(this.templateForm)

        const response = await axios.post("/templates", this.templateForm);

        if (response.data.success) {
          this.showSuccessMessage(response.data.message);
          this.resetTemplateForm();
          const modal = Modal.getInstance(document.getElementById("add-template"));
          modal.hide();
          await this.fetchTemplates(this.pagination.current_page);
        }
      } catch (error) {
        console.error("Erreur lors de la création:", error);
        
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          let errorMessage = "Erreurs de validation:\n";
          Object.keys(errors).forEach(field => {
            errorMessage += `- ${errors[field].join(', ')}\n`;
          });
          this.showErrorMessage(errorMessage);
        } else {
          this.showErrorMessage("Erreur lors de la création du template");
        }
      }
    },

    async updateTemplate() {
      try {
        // Validate JSON format for variables
        try {
          JSON.parse(this.editTemplateForm.variables);
        } catch (e) {
          this.showErrorMessage("Le format JSON des variables est invalide");
          return;
        }

        const { id, ...updateData } = this.editTemplateForm;
        const response = await axios.put(`/templates/${id}`, updateData);

        if (response.data.success) {
          this.showSuccessMessage(response.data.message);
          const modal = Modal.getInstance(document.getElementById("edit-template"));
          modal.hide();
          await this.fetchTemplates(this.pagination.current_page);
        }
      } catch (error) {
        console.error("Erreur lors de la mise à jour:", error);
        
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          let errorMessage = "Erreurs de validation:\n";
          Object.keys(errors).forEach(field => {
            errorMessage += `- ${errors[field].join(', ')}\n`;
          });
          this.showErrorMessage(errorMessage);
        } else {
          this.showErrorMessage("Erreur lors de la mise à jour du template");
        }
      }
    },

    resetTemplateForm() {
      this.templateForm = {
        name: '',
        description: '',
        content: '',
        type: '',
        document_type: '',
        variables: '{}',
        version: 'v1.0',
        is_active: true
      };
    },

    async toggleTemplateStatus(template) {
      try {
        const response = await axios.post(`/templates/${template.id}/toggle-active`, {
          is_active: !template.is_active
        });

        if (response.data.success) {
          template.is_active = !template.is_active;
          this.showSuccessMessage(response.data.message);
        }
      } catch (error) {
        console.error("Erreur lors du changement de statut:", error);
        this.showErrorMessage("Erreur lors du changement de statut");
      }
    },

    // TinyMCE Integration Methods
    async loadTinyMCE() {
      return new Promise((resolve, reject) => {
        if (window.tinymce) {
          resolve();
          return;
        }

        const script = document.createElement("script");
        script.src = "https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js";
        script.onload = () => resolve();
        script.onerror = () => reject(new Error("Failed to load TinyMCE"));
        document.head.appendChild(script);
      });
    },

    async initTinyMCE() {
      try {
        await this.loadTinyMCE();

        if (window.tinymce) {
          window.tinymce.remove("#tinymce-editor");
        }

        await this.$nextTick();

        await window.tinymce.init({
          selector: "#tinymce-editor",
          height: "100%",
          min_height: 400,
          max_height: 2000,
          resize: false,
          auto_focus: true,
          branding: false,
          menubar: true,
          plugins: [
            "advlist", "autolink", "lists", "link", "image", "charmap", "preview",
            "anchor", "searchreplace", "visualblocks", "code", "fullscreen",
            "insertdatetime", "media", "table", "help", "wordcount", "directionality",
          ],
          toolbar: [
            "undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify",
            "bullist numlist outdent indent | removeformat | ltr rtl | help",
            "table | link image | code preview fullscreen",
          ],
          content_style: `
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
              margin: 15px;
              direction: ${this.contentDirection};
              height: 100%;
            }
          `,
          setup: (editor) => {
            this.tinymceEditor = editor;

            editor.on("Change", () => {
              this.editingTemplate.content = editor.getContent();
            });

            editor.on("init", () => {
              editor.setContent(this.editingTemplate.content || "");
              this.tinymceInitialized = true;

              setTimeout(() => {
                this.resizeEditor();
              }, 100);
            });

            window.addEventListener('resize', this.resizeEditor);
          },
        });
      } catch (error) {
        console.error("Error initializing TinyMCE:", error);
        this.showErrorMessage("Erreur lors de l'initialisation de l'éditeur");
      }
    },

    resizeEditor() {
      if (this.tinymceEditor && this.tinymceInitialized) {
        try {
          const container = document.querySelector('.editor-container');
          if (container) {
            const containerHeight = container.clientHeight;
            const toolbarHeight = 80;
            const editorHeight = Math.max(400, containerHeight - toolbarHeight);
            this.tinymceEditor.theme.resizeTo('100%', editorHeight);
          }
        } catch (error) {
          console.error("Error resizing editor:", error);
        }
      }
    },

    setTinyMCEDirection(direction, editor = null) {
      const activeEditor = editor || this.tinymceEditor;
      if (activeEditor) {
        const body = activeEditor.getBody();
        if (body) {
          body.style.direction = direction;
          body.style.textAlign = direction === "rtl" ? "right" : "left";
        }
        this.contentDirection = direction;
      }
    },

    async destroyTinyMCE() {
      if (window.tinymce) {
        try {
          window.removeEventListener('resize', this.resizeEditor);
          await window.tinymce.remove("#tinymce-editor");
          this.tinymceEditor = null;
          this.tinymceInitialized = false;
        } catch (error) {
          console.error("Error destroying TinyMCE:", error);
        }
      }
    },

    async openContentEditor(template) {
      // Ensure all required fields are present
      this.editingTemplate = {
        id: template.id,
        name: template.name,
        description: template.description || '',
        content: template.content || '',
        type: template.type,
        document_type: template.document_type,
        variables: template.variables || {},
        version: template.version || 'v1.0',
        is_active: template.is_active !== undefined ? template.is_active : true
      };
      
      this.contentDirection = "auto";

      const modal = new Modal(document.getElementById("content-editor-modal"));
      const modalElement = document.getElementById("content-editor-modal");
      
      modalElement.addEventListener("hidden.bs.modal", async () => {
        await this.destroyTinyMCE();
        this.fixBodyScroll();
      }, { once: true });

      modal.show();

      modalElement.addEventListener("shown.bs.modal", async () => {
        await this.$nextTick();
        setTimeout(async () => {
          await this.initTinyMCE();
        }, 200);
      }, { once: true });
    },

    setContentDirection(direction) {
      this.contentDirection = direction;

      if (this.tinymceInitialized && this.tinymceEditor) {
        const actualDirection = direction === "auto" 
          ? this.detectContentDirection(this.editingTemplate.content) 
          : direction;
        this.setTinyMCEDirection(actualDirection);
      }
    },

    async saveContent() {
      try {
        if (this.tinymceEditor) {
          this.editingTemplate.content = this.tinymceEditor.getContent();
        }

        // Ensure all required fields are present and properly formatted
        const updateData = {
          name: this.editingTemplate.name,
          description: this.editingTemplate.description || '',
          content: this.editingTemplate.content,
          type: this.editingTemplate.type,
          document_type: this.editingTemplate.document_type,
          variables: typeof this.editingTemplate.variables === 'string' 
            ? this.editingTemplate.variables 
            : JSON.stringify(this.editingTemplate.variables || {}),
          version: this.editingTemplate.version,
          is_active: this.editingTemplate.is_active !== undefined ? this.editingTemplate.is_active : true
        };

        const response = await axios.put(`/templates/${this.editingTemplate.id}`, updateData);

        if (response.data.success) {
          const index = this.templates.findIndex(t => t.id === this.editingTemplate.id);
          if (index !== -1) {
            this.templates[index] = response.data.data;
          }

          const modal = Modal.getInstance(document.getElementById("content-editor-modal"));
          modal.hide();

          this.showSuccessMessage(response.data.message);
        }
      } catch (error) {
        console.error("Erreur lors de la sauvegarde:", error);
        
        // Handle validation errors specifically
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          let errorMessage = "Erreurs de validation:\n";
          Object.keys(errors).forEach(field => {
            errorMessage += `- ${errors[field].join(', ')}\n`;
          });
          this.showErrorMessage(errorMessage);
        } else {
          this.showErrorMessage("Erreur lors de la sauvegarde du contenu");
        }
      }
    },

    // Utility Methods
    formatDate(dateString) {
      if (!dateString) return "N/A";
      const date = new Date(dateString);
      return date.toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    },

    truncateText(text, maxLength) {
      if (!text) return "";
      return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
    },

    getStatusClass(isActive) {
      return isActive ? "status-badge-active" : "status-badge-inactive";
    },

    getStatusText(isActive) {
      return isActive ? "Actif" : "Inactif";
    },

    getDocumentTypeLabel(type) {
      const labels = {
        'CONTRAT_DOMICILIATION': 'Contrat de Domiciliation',
        'ATTESTATION_DOMICILIATION': 'Attestation de Domiciliation',
        'FACTURE': 'Facture',
        'RECU_PAIEMENT': 'Reçu de Paiement',
        'CERTIFICAT': 'Certificat',
        'AUTRE': 'Autre'
      };
      return labels[type] || type;
    },

    getVariableCount(variables) {
      if (!variables) return 0;
      try {
        const parsed = typeof variables === "string" ? JSON.parse(variables) : variables;
        return Object.keys(parsed).length;
      } catch (error) {
        return 0;
      }
    },

    detectContentDirection(content) {
      if (!content) return "ltr";
      const textContent = content.replace(/<[^>]*>/g, "");
      const rtlChars = /[\u0590-\u05FF\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
      return rtlChars.test(textContent) ? "rtl" : "ltr";
    },

    getContentDirectionClass(content) {
      if (this.contentDirection === "auto") {
        const detectedDirection = this.detectContentDirection(content);
        return `content-direction-${detectedDirection}`;
      }
      return `content-direction-${this.contentDirection}`;
    },

    sanitizeContent(content) {
      if (!content) return "";
      let sanitized = content.replace(/<\/?(?:html|body)[^>]*>/gi, "");
      sanitized = sanitized.replace(/\bbody\s*\{[^}]*direction[^}]*\}/gi, "");
      sanitized = sanitized.replace(/\bhtml\s*\{[^}]*direction[^}]*\}/gi, "");
      return sanitized;
    },

    // Navigation Methods
    goToPage(page) {
      this.pagination.current_page = page;
      this.fetchTemplates(page);
    },

    previousPage() {
      if (this.pagination.current_page > 1) {
        this.goToPage(this.pagination.current_page - 1);
      }
    },

    nextPage() {
      if (this.pagination.current_page < this.pagination.last_page) {
        this.goToPage(this.pagination.current_page + 1);
      }
    },

    handleItemsPerPageChange() {
      this.pagination.current_page = 1;
      this.fetchTemplates();
    },

    // Modal Methods
    openAddModal() {
      this.resetTemplateForm();
      const modal = new Modal(document.getElementById("add-template"));
      const modalElement = document.getElementById("add-template");
      modalElement.addEventListener("hidden.bs.modal", this.fixBodyScroll, { once: true });
      modal.show();
    },

    async fetchTemplateDetails(templateId) {
      try {
        const response = await axios.get(`/templates/${templateId}`);
        if (response.data.success) {
          return response.data.data;
        }
      } catch (error) {
        console.error("Erreur lors du chargement des détails du template:", error);
        this.showErrorMessage("Erreur lors du chargement des détails du template");
      }
      return null;
    },

    async openEditModal(template) {
      // Fetch complete template data to ensure all fields are present
      const completeTemplate = await this.fetchTemplateDetails(template.id);
      if (completeTemplate) {
        this.editTemplateForm = {
          id: completeTemplate.id,
          name: completeTemplate.name,
          description: completeTemplate.description || '',
          content: completeTemplate.content,
          type: completeTemplate.type,
          document_type: completeTemplate.document_type,
          variables: typeof completeTemplate.variables === 'string' 
            ? completeTemplate.variables 
            : JSON.stringify(completeTemplate.variables || {}),
          version: completeTemplate.version,
          is_active: completeTemplate.is_active
        };

        const modal = new Modal(document.getElementById("edit-template"));
        const modalElement = document.getElementById("edit-template");
        modalElement.addEventListener("hidden.bs.modal", this.fixBodyScroll, { once: true });
        modal.show();
      }
    },

    viewTemplate(template) {
      this.viewingTemplate = template;
      const modal = new Modal(document.getElementById("view-template"));
      const modalElement = document.getElementById("view-template");
      modalElement.addEventListener("hidden.bs.modal", this.fixBodyScroll, { once: true });
      modal.show();
    },

    async downloadTemplate(template, format) {
      try {
        if (format === "word") {
          await this.generateWord(template);
        }
        this.showSuccessMessage(`Template téléchargé en ${format.toUpperCase()}`);
      } catch (error) {
        console.error("Erreur lors du téléchargement:", error);
        this.showErrorMessage("Erreur lors du téléchargement du template");
      }
    },

    async generateWord(template) {
      const children = [
        new Paragraph({
          children: [
            new TextRun({
              text: template.name,
              bold: true,
              size: 32,
              color: "2E74B5",
            }),
          ],
          heading: HeadingLevel.TITLE,
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: `Type: ${template.type} | Document: ${this.getDocumentTypeLabel(template.document_type)}`,
              size: 24,
            }),
          ],
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: template.description || "Aucune description",
              size: 24,
            }),
          ],
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: template.content?.replace(/<[^>]*>/g, '') || "Aucun contenu",
              size: 24,
            }),
          ],
        }),
      ];

      const doc = new Document({
        sections: [{ children }],
      });

      const blob = await Packer.toBlob(doc);
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", `${template.name}.docx`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    },

    async confirmDeleteTemplate(templateId) {
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
      });

      if (result.isConfirmed) {
        try {
          const response = await axios.delete(`/templates/${templateId}`);
          if (response.data.success) {
            await this.fetchTemplates(this.pagination.current_page);
            this.showSuccessMessage(response.data.message);
          }
        } catch (error) {
          console.error("Erreur lors de la suppression:", error);
          this.showErrorMessage("Erreur lors de la suppression du template");
        }
      }
    },

    fixBodyScroll() {
      document.body.classList.remove("modal-open");
      document.body.style.overflow = "";
      document.body.style.paddingRight = "";
      document.body.style.direction = "ltr";

      const backdrops = document.querySelectorAll(".modal-backdrop");
      backdrops.forEach((backdrop) => backdrop.remove());
    },

    showSuccessMessage(message) {
      Swal.fire({
        title: "Succès!",
        text: message,
        icon: "success",
        confirmButtonText: "OK",
        confirmButtonClass: "btn btn-success",
        buttonsStyling: false,
        position: "top-end",
        toast: true,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
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
        position: "top-end",
        toast: true,
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: false,
      });
    },
  },

  async mounted() {
    await this.fetchTemplates();
    this.fixBodyScroll();
  },

  async beforeUnmount() {
    await this.destroyTinyMCE();
    this.fixBodyScroll();
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
    }
  },
};
</script>

<style scoped>
/* ==================== Modern Header Styles ==================== */
.modern-header {
  background: #ABC270 !important;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.page-title-section {
  display: flex;
  align-items: center;
  gap: 2rem;
  flex: 1;
}

.title-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.icon-wrapper {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.title-content h4 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: white;
}

.page-subtitle {
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
  font-size: 0.95rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.export-actions {
  display: flex;
  gap: 0.5rem;
  margin-right: 1rem;
}

.export-btn {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.export-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

.btn-added {
  background: rgba(255, 255, 255, 0.9);
  color: #ABC270;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-added:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* ==================== Filter Card Styles ==================== */
.filter-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
  overflow: hidden;
}

.filter-card .card-header {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-weight: 600;
  color: #495057;
  display: flex;
  align-items: center;
}

.total-templates-badge {
  background: rgba(171, 194, 112, 0.1);
  color: #ABC270;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
}

.search-input-wrapper {
  position: relative;
}

.search-input {
  padding-right: 2.5rem;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.search-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.search-icon {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

.form-control,
.form-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.75rem;
  transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* ==================== Templates Grid Styles ==================== */
.templates-grid {
  margin-bottom: 2rem;
}

.template-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
}

.template-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.template-card-header {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.template-card-body {
  padding: 1.5rem;
}

.template-icon-wrapper {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, #ABC270, #8fa85b);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  box-shadow: 0 4px 15px rgba(171, 194, 112, 0.3);
}

.template-icon {
  color: white;
}

.template-version {
  color: #6c757d;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.template-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0.5rem 0;
  line-height: 1.3;
}

.template-description {
  color: #6c757d;
  font-size: 0.875rem;
  line-height: 1.4;
  margin-bottom: 1rem;
}

.template-badges {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  flex-wrap: wrap;
}

.template-type-badge {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-weight: 600;
}

.template-doc-badge {
  background: linear-gradient(135deg, #95a5a6, #7f8c8d);
  color: white;
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-weight: 600;
}

.template-stats {
  border-top: 1px solid #f1f3f4;
  border-bottom: 1px solid #f1f3f4;
  margin: 1rem 0;
  padding: 1rem 0;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-value {
  font-weight: 700;
  color: #2c3e50;
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.stat-label {
  color: #6c757d;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.template-actions {
  margin-top: 1rem;
}

.btn-template-view {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-template-view:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  color: white;
}

.btn-template-download {
  background: linear-gradient(135deg, #28a745, #20c997);
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-template-download:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
  color: white;
}

.status-badge-active {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge-inactive {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.action-icon-modern {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  color: #6c757d;
}

.action-icon-modern:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #495057;
}

.dropdown-menu-modern {
  border: none;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border-radius: 12px;
  padding: 0.5rem 0;
}

.dropdown-icon {
  width: 16px;
  height: 16px;
  margin-right: 0.5rem;
}

/* ==================== Pagination Styles ==================== */
.pagination-wrapper {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  padding: 1.5rem;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.pagination-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.entries-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.entries-label {
  font-weight: 600;
  color: #495057;
  font-size: 0.875rem;
}

.entries-select {
  width: auto;
  min-width: 80px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  padding: 0.375rem 0.75rem;
}

.entries-text {
  color: #6c757d;
  font-size: 0.875rem;
}

.records-info {
  display: flex;
  align-items: center;
}

.records-text {
  color: #495057;
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  width: 40px;
  height: 40px;
  border: 1px solid #dee2e6;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.pagination-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-color: #667eea;
  color: white;
  transform: translateY(-2px);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  margin: 0 1rem;
}

.page-text,
.page-total {
  color: #6c757d;
  font-size: 0.875rem;
}

.page-select {
  width: 70px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 0.25rem 0.5rem;
  text-align: center;
}

/* ==================== Editor Modal Styles ==================== */
.editor-modal-content {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.editor-modal-body {
  flex: 1;
  padding: 0 !important;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.editor-layout {
  display: flex;
  height: 100%;
  flex: 1;
}

.editor-section {
  width: 50%;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #dee2e6;
  padding: 1rem;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  flex-shrink: 0;
}

.editor-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.preview-section {
  width: 50%;
  display: flex;
  flex-direction: column;
  padding: 1rem;
}

.preview-container {
  flex: 1;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 15px;
  background-color: #f8f9fa;
  overflow-y: auto;
}

/* TinyMCE specific styles */
:deep(.tox.tox-tinymce) {
  height: 100% !important;
  display: flex !important;
  flex-direction: column !important;
  border: 1px solid #dee2e6 !important;
  border-radius: 4px !important;
}

:deep(.tox-editor-container) {
  display: flex !important;
  flex-direction: column !important;
  flex: 1 !important;
}

:deep(.tox-edit-area) {
  flex: 1 !important;
  display: flex !important;
  flex-direction: column !important;
}

:deep(.tox-edit-area__iframe) {
  flex: 1 !important;
  height: auto !important;
}

:deep(.tox-toolbar-overlord) {
  flex-shrink: 0 !important;
}

:deep(.tox-menubar) {
  flex-shrink: 0 !important;
}

/* Direction specific styles */
.content-direction-rtl {
  direction: rtl !important;
  text-align: right !important;
  font-family: Arial, sans-serif;
}

.content-direction-ltr {
  direction: ltr !important;
  text-align: left !important;
  font-family: Arial, sans-serif;
}

/* Template preview container */
.template-preview-container {
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 15px;
  background-color: white;
  height: 100%;
  overflow-y: auto;
  max-height: 600px;
}

/* Modal styles */
.modal-dialog-centered.modal-fullscreen {
  display: flex;
  align-items: stretch;
  margin: 0;
  width: 100vw;
  max-width: none;
  height: 100vh;
}

.modal-content {
  display: flex;
  flex-direction: column;
  height: 100%;
  border-radius: 0;
}

/* Direction controls styling */
.direction-controls .btn-group {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.direction-controls .btn {
  border-color: #dee2e6;
}

/* ==================== Responsive Design ==================== */
@media (max-width: 1200px) {
  .filter-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

@media (max-width: 768px) {
  .modern-header {
    padding: 1.5rem;
  }

  .header-content {
    flex-direction: column;
    align-items: stretch;
  }

  .page-title-section {
    flex-direction: column;
    gap: 1rem;
  }

  .title-wrapper {
    flex-direction: column;
    text-align: center;
  }

  .header-actions {
    flex-direction: column;
    gap: 1rem;
  }

  .export-actions {
    justify-content: center;
    margin-right: 0;
  }

  .filter-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .pagination-info {
    flex-direction: column;
    gap: 1rem;
  }

  .pagination-controls {
    flex-wrap: wrap;
  }

  .page-selector {
    margin: 0;
  }

  .editor-layout {
    flex-direction: column;
  }
  
  .editor-section,
  .preview-section {
    width: 100%;
  }
  
  .editor-section {
    border-right: none;
    border-bottom: 1px solid #dee2e6;
  }
}

@media (max-width: 576px) {
  .modern-header {
    padding: 1rem;
  }

  .icon-wrapper {
    width: 50px;
    height: 50px;
    font-size: 1.25rem;
  }

  .title-content h4 {
    font-size: 1.5rem;
  }

  .export-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
  }

  .template-card-body {
    padding: 1rem;
  }

  .template-icon-wrapper {
    width: 60px;
    height: 60px;
  }
}
</style>

