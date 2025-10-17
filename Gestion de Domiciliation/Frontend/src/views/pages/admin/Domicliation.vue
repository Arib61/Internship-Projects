<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <!-- Enhanced Header Section - Couleur modifiée -->
      <div class="page-header modern-header" style="background-color: #ABC270;">
        <div class="header-content">
          <div class="page-title-section">
            <div class="title-wrapper">
              <div class="icon-wrapper">
                <i class="fas fa-building text-primary"></i>
              </div>
              <div class="title-content">
                <h4 class="page-title">Gestion des Domiciliations</h4>
                <p class="page-subtitle">Gérez vos contrats de domiciliation en toute simplicité</p>
              </div>
            </div>
          </div>

          <div class="header-actions">
            <div class="export-actions">
              <button class="action-btn export-btn" @click="exportToPDF" data-bs-toggle="tooltip"
                title="Exporter en PDF">
                <i class="fas fa-file-pdf"></i>

              </button>
              <button class="action-btn export-btn" @click="exportToExcel" data-bs-toggle="tooltip"
                title="Exporter en Excel">
                <i class="fas fa-file-excel"></i>

              </button>

            </div>


            <button class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#add-domiciliation">
              <i class="fas fa-plus-circle me-2"></i>
              <span>Nouvelle Domiciliation</span>
            </button>
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
          <button class="btn btn-sm btn-outline-secondary" @click="clearFilters">
            <i class="fas fa-times me-1"></i>
            Effacer
          </button>
        </div>
        <div class="card-body">
          <div class="filter-grid">
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-search me-1"></i>
                Recherche générale
              </label>
              <div class="search-input-wrapper">
                <input type="text" class="form-control search-input" placeholder="Rechercher..." v-model="search"
                  @input="filterDomiciliations" />
                <i class="fas fa-search search-icon"></i>
              </div>
            </div>



            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-user-tie me-1"></i>
                Client
              </label>
              <select class="form-select" v-model="filters.client" @change="filterDomiciliations">
                <option value="">Tous les clients</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.nom_francais }}
                </option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-tasks me-1"></i>
                Situation
              </label>
              <select class="form-select" v-model="filters.situation" @change="filterDomiciliations">
                <option value="">Toutes</option>
                <option value="DEMANDE">Demande</option>
                <option value="EN_COURS">En cours</option>
                <option value="REJETTE">Rejetée</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-clock me-1"></i>
                Statut expiration
              </label>
              <select class="form-select" v-model="filters.expiration" @change="filterDomiciliations">
                <option value="">Tous</option>
                <option value="expired">Expirés</option>
                <option value="danger">Critique (≤5 jours)</option>
                <option value="warning">Attention (≤15 jours)</option>
                <option value="success">Valides (>15 jours)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Data Table Card -->
      <div class="card data-table-card">
        <div class="card-body p-0">
          <div class="table-container">
            <div class="table-responsive">
              <a-table class="modern-table" :columns="columns" :data-source="filteredData" :row-selection="{}"
                :pagination="false">
                <template #bodyCell="{ column, record }">
                  <template v-if="column.key === 'reference'">
                    <div class="reference-cell">
                      <span class="reference-badge">{{ record.reference_numero || "-" }}</span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'client'">
                    <div class="client-cell">

                      <span class="client-name">{{ record.client_nom }}</span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'gestionnaire'">
                    <div class="gestionnaire-cell">

                      <span>{{ record.gestionnaire_nom }}</span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'date_debut'">
                    <div class="date-cell">
                      <i class="fas fa-calendar-alt me-1"></i>
                      {{ formatDate(record.date_debut) }}
                    </div>
                  </template>
                  <template v-else-if="column.key === 'date_fin'">
                    <div class="date-cell">
                      <i class="fas fa-calendar-check me-1"></i>
                      {{ formatDate(record.date_fin) }}
                    </div>
                  </template>
                  <template v-else-if="column.key === 'time_remaining'">
                    <div class="time-remaining-cell">
                      <span :class="getTimeRemainingClass(record.date_fin)">
                        <i class="fas fa-hourglass-half me-1"></i>
                        {{ getTimeRemainingText(record.date_fin) }}
                      </span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'duree'">
                    <div class="duration-cell">
                      <span class="duration-badge">
                        <i class="fas fa-clock me-1"></i>
                        {{ record.duree_text }}
                      </span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'montant'">
                    <div class="amount-cell">
                      <span class="amount-value">{{ formatMontant(record.montant) }}</span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'situation'">
                    <div class="status-cell">
                      <span :class="getSituationClass(record.situation)">
                        <i class="fas fa-circle me-1"></i>
                        {{ getSituationText(record.situation) }}
                      </span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'payement'">
                    <div class="payment-cell">
                      <span :class="record.payement ? 'payment-badge paid' : 'payment-badge unpaid'">
                        <i :class="record.payement ? 'fas fa-check-circle' : 'fas fa-times-circle'" class="me-1"></i>
                        {{ record.payement ? "Payé" : "Non payé" }}
                      </span>
                    </div>
                  </template>
                  <template v-else-if="column.key === 'action'">
                    <div class="action-cell">
                      <div class="action-buttons">
                        <button class="action-btn edit-action" data-bs-toggle="modal"
                          data-bs-target="#edit-domiciliation" @click.prevent="openEditModal(record)" title="Modifier">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn renew-action" data-bs-toggle="modal"
                          data-bs-target="#renew-domiciliation" @click.prevent="openRenewModal(record)"
                          title="Renouveler" :disabled="isSubmitting">
                          <i class="fas fa-sync-alt"></i>
                          <span v-if="isSubmitting && renewingId === record.id"
                            class="spinner-border spinner-border-sm ms-1"></span>
                        </button>
                        <button class="action-btn download-action" @click.prevent="downloadContract(record)"
                          :disabled="isDownloading && downloadingId === record.id"
                          title="Télécharger le contrat (PDF + Word)">
                          <!-- Icône si pas en cours -->
                          <VueFeather v-if="!(isDownloading && downloadingId === record.id)" type="download"
                            size="16" />

                          <!-- Spinner si en cours -->
                          <div v-else class="spinner-border spinner-border-sm ms-1" role="status">
                            <span class="sr-only">Téléchargement...</span>
                          </div>
                        </button>

                        <button class="action-btn delete-action" @click.prevent="onDeleteDomiciliation(record)"
                          title="Supprimer">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </div>
                  </template>
                </template>
              </a-table>
            </div>
          </div>

          <!-- Enhanced Pagination -->
          <div class="pagination-wrapper">
            <div class="pagination-info">
              <div class="entries-selector">
                <label class="entries-label">Afficher :</label>
                <select v-model="pageSize" class="form-select entries-select" @change="onPageSizeChange">
                  <option value="5">5</option>
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
                <span class="entries-text">entrées par page</span>
              </div>

              <div class="records-info">
                <span class="records-text">
                  Affichage de <strong>{{ startRecord }}</strong> à <strong>{{ endRecord }}</strong>
                  sur <strong>{{ allFilteredData.length }}</strong> entrées
                </span>
              </div>
            </div>

            <div class="pagination-controls">
              <button class="pagination-btn first-btn" :disabled="currentPage === 1" @click="goToPage(1)">
                <i class="fas fa-angle-double-left"></i>
              </button>
              <button class="pagination-btn prev-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
                <i class="fas fa-angle-left"></i>
              </button>

              <div class="page-selector">
                <span class="page-text">Page :</span>
                <select v-model="currentPage" class="form-select page-select" @change="onPageChange">
                  <option v-for="page in totalPages" :key="page" :value="page">{{ page }}</option>
                </select>
                <span class="page-total">sur {{ totalPages }}</span>
              </div>

              <button class="pagination-btn next-btn" :disabled="currentPage === totalPages"
                @click="goToPage(currentPage + 1)">
                <i class="fas fa-angle-right"></i>
              </button>
              <button class="pagination-btn last-btn" :disabled="currentPage === totalPages"
                @click="goToPage(totalPages)">
                <i class="fas fa-angle-double-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Enhanced Modal Ajouter Domiciliation -->
  <div class="modal fade" id="add-domiciliation" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content modern-modal">
        <div class="modal-header">
          <div class="modal-title-wrapper">
            <div class="modal-icon">
              <i class="fas fa-plus-circle"></i>
            </div>
            <div class="modal-title-content">
              <h5 class="modal-title">Ajouter une Domiciliation</h5>
              <p class="modal-subtitle">Créer un nouveau contrat de domiciliation</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveDomiciliation" class="modern-form">
            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-user-friends me-2"></i>
                Informations Client & Gestionnaire
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user-tie me-1"></i>
                      Client
                    </label>
                    <div class="input-group">
                      <select v-model="addFormData.client_id" class="form-select" required>
                        <option value="" disabled>Sélectionner un client</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">
                          {{ client.nom_francais }}
                        </option>
                      </select>
                      <button type="button" class="btn btn-outline-primary" @click="redirectToAddClient">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user-cog me-1"></i>
                      Gestionnaire
                    </label>
                    <div class="input-group">
                      <select v-model="renewFormData.gestionnaire_id" class="form-select" required>
                        <option value="" disabled>Sélectionner un gestionnaire</option>
                        <option v-for="gestionnaire in gestionnaires" :key="gestionnaire.id" :value="gestionnaire.id">
                          {{ gestionnaire.nom }} {{ gestionnaire.prenom }}
                        </option>
                      </select>
                      <button v-if="gestionnaires.length > 1" type="button" class="btn btn-primary"
                        @click="openAddGestionnaireModal">
                        <VueFeather type="plus" size="16" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-calendar-alt me-2"></i>
                Période & Durée
              </h6>
              <div class="row g-4">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-play me-1"></i>
                      Date de début
                    </label>
                    <input type="date" class="form-control" v-model="addFormData.date_debut"
                      @change="calculateEndDateForAdd" required />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-clock me-1"></i>
                      Durée
                    </label>
                    <select class="form-select" v-model="addFormData.duree_mois" @change="calculateEndDateForAdd"
                      required>
                      <option value="" disabled>Sélectionner une durée</option>
                      <option value="3">3 mois</option>
                      <option value="6">6 mois (demi-année)</option>
                      <option value="12">1 an</option>
                      <option value="15">1 an et 3 mois</option>
                      <option value="18">1 an et demi</option>
                      <option value="24">2 ans</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-stop me-1"></i>
                      Date de fin
                    </label>
                    <input type="date" class="form-control" v-model="addFormData.date_fin" readonly />
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-money-bill-wave me-2"></i>
                Montant & Statut
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-dollar-sign me-1"></i>
                      Montant
                    </label>
                    <div class="input-group">
                      <input type="number" step="0.01" class="form-control" v-model="addFormData.montant" required />
                      <span class="input-group-text">MAD</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-tasks me-1"></i>
                      Situation
                    </label>
                    <select class="form-select" v-model="addFormData.situation" required>
                      <option value="EN_COURS">En cours</option>
                      <option value="DEMANDE">Demande</option>
                      <option value="REJETTE">Rejetée</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-info-circle me-2"></i>
                Informations Complémentaires
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-credit-card me-1"></i>
                      Paiement
                    </label>
                    <div class="form-check form-switch payment-switch">
                      <input class="form-check-input" type="checkbox" v-model="addFormData.payement"
                        id="payementSwitch" />
                      <label class="form-check-label" for="payementSwitch">
                        <span class="switch-text">{{ addFormData.payement ? "Payé" : "Non payé" }}</span>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-hashtag me-1"></i>
                      Numéro de référence
                    </label>
                    <input type="text" class="form-control" v-model="addFormData.reference_numero" maxlength="50"
                      readonly />
                    <small class="form-text">Généré automatiquement</small>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">
                  <i class="fas fa-sticky-note me-1"></i>
                  Notes
                </label>
                <textarea class="form-control" v-model="addFormData.notes" rows="3"
                  placeholder="Ajouter des notes..."></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>
            Annuler
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting" @click="saveDomiciliation">
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="fas fa-save me-1"></i>
            {{ isSubmitting ? 'Création en cours...' : 'Enregistrer' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Enhanced Modal Modifier Domiciliation -->
  <div class="modal fade" id="edit-domiciliation" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content modern-modal">
        <div class="modal-header">
          <div class="modal-title-wrapper">
            <div class="modal-icon edit-icon">
              <i class="fas fa-edit"></i>
            </div>
            <div class="modal-title-content">
              <h5 class="modal-title">Modifier la Domiciliation</h5>
              <p class="modal-subtitle">Mettre à jour les informations du contrat</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="updateDomiciliation" class="modern-form">
            <!-- Same form structure as add modal but with editFormData -->
            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-user-friends me-2"></i>
                Informations Client & Gestionnaire
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user-tie me-1"></i>
                      Client
                    </label>
                    <div class="input-group">
                      <select v-model="editFormData.client_id" class="form-select" required>
                        <option value="" disabled>Sélectionner un client</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">
                          {{ client.nom_francais }}
                        </option>
                      </select>
                      <button type="button" class="btn btn-outline-primary" @click="redirectToAddClient">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user-cog me-1"></i>
                      Gestionnaire
                    </label>
                    <div class="input-group">
                      <select v-model="editFormData.gestionnaire_id" class="form-select" required>
                        <option value="" disabled>Sélectionner un gestionnaire</option>
                        <option v-for="gestionnaire in gestionnaires" :key="gestionnaire.id" :value="gestionnaire.id">
                          {{ gestionnaire.nom }} {{ gestionnaire.prenom }}
                        </option>
                      </select>
                      <button type="button" class="btn btn-outline-primary" @click="openAddGestionnaireModal">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-calendar-alt me-2"></i>
                Période & Durée
              </h6>
              <div class="row g-4">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-play me-1"></i>
                      Date de début
                    </label>
                    <input type="date" class="form-control" v-model="editFormData.date_debut"
                      @change="calculateEndDateForEdit" required />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-clock me-1"></i>
                      Durée
                    </label>
                    <select class="form-select" v-model="editFormData.duree_mois" @change="calculateEndDateForEdit"
                      required>
                      <option value="" disabled>Sélectionner une durée</option>
                      <option value="3">3 mois</option>
                      <option value="6">6 mois (demi-année)</option>
                      <option value="12">1 an</option>
                      <option value="15">1 an et 3 mois</option>
                      <option value="18">1 an et demi</option>
                      <option value="24">2 ans</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-stop me-1"></i>
                      Date de fin
                    </label>
                    <input type="date" class="form-control" v-model="editFormData.date_fin" readonly />
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-money-bill-wave me-2"></i>
                Montant & Statut
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-dollar-sign me-1"></i>
                      Montant
                    </label>
                    <div class="input-group">
                      <input type="number" step="0.01" class="form-control" v-model="editFormData.montant" required />
                      <span class="input-group-text">MAD</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-tasks me-1"></i>
                      Situation
                    </label>
                    <select class="form-select" v-model="editFormData.situation" required>
                      <option value="DEMANDE">Demande</option>
                      <option value="EN_COURS">En cours</option>
                      <option value="REJETTE">Rejetée</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-info-circle me-2"></i>
                Informations Complémentaires
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-credit-card me-1"></i>
                      Paiement
                    </label>
                    <div class="form-check form-switch payment-switch">
                      <input class="form-check-input" type="checkbox" v-model="editFormData.payement"
                        id="editPayementSwitch" />
                      <label class="form-check-label" for="editPayementSwitch">
                        <span class="switch-text">{{ editFormData.payement ? "Payé" : "Non payé" }}</span>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-hashtag me-1"></i>
                      Numéro de référence
                    </label>
                    <input type="text" class="form-control" v-model="editFormData.reference_numero" maxlength="50"
                      readonly />
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">
                  <i class="fas fa-sticky-note me-1"></i>
                  Notes
                </label>
                <textarea class="form-control" v-model="editFormData.notes" rows="3"
                  placeholder="Ajouter des notes..."></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>
            Annuler
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting" @click="updateDomiciliation">
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="fas fa-save me-1"></i>
            Mettre à jour
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Enhanced Modal Renouveler Domiciliation -->
  <div class="modal fade" id="renew-domiciliation" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content modern-modal">
        <div class="modal-header" style="background-color: #ABC270;">
          <div class="modal-title-wrapper">
            <div class="modal-icon renew-icon">
              <i class="fas fa-sync-alt"></i>
            </div>
            <div class="modal-title-content">
              <h5 class="modal-title">Renouveler la Domiciliation</h5>
              <p class="modal-subtitle">Créer un nouveau contrat basé sur l'existant</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Section d'information sur le contrat actuel -->
          <div class="contract-info-card" v-if="originalDomiciliation">
            <div class="contract-info-header">
              <h6 class="mb-0">Renouvellement de contrat</h6>
            </div>
            <div class="contract-details">
              <div class="row">
                <div class="col-md-6">
                  <div class="detail-item">
                    <span class="detail-label">Référence actuelle:</span>
                    <span class="detail-value">{{ originalDomiciliation.reference_numero }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Client:</span>
                    <span class="detail-value">{{ originalDomiciliation.client_nom }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Date fin actuelle:</span>
                    <span class="detail-value">{{ formatDate(originalDomiciliation.date_fin) }}</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="detail-item">
                    <span class="detail-label">Montant actuel:</span>
                    <span class="detail-value">{{ formatMontant(originalDomiciliation.montant) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Durée actuelle:</span>
                    <span class="detail-value">{{ originalDomiciliation.duree_text }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Gestionnaire:</span>
                    <span class="detail-value">{{ originalDomiciliation.gestionnaire_nom }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="contract-note">
              <i class="fas fa-info-circle me-1"></i>
              L'ancien contrat sera automatiquement archivé lors du renouvellement.
            </div>
          </div>

          <form @submit.prevent="renewDomiciliation" class="modern-form">
            <!-- Same form structure as add modal but with renewFormData -->
            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-user-friends me-2"></i>
                Informations Client & Gestionnaire
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user-tie me-1"></i>
                      Client
                    </label>
                    <div class="input-group">
                      <select v-model="renewFormData.client_id" class="form-select" required>
                        <option value="" disabled>Sélectionner un client</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">
                          {{ client.nom_francais }}
                        </option>
                      </select>
                      <button type="button" class="btn btn-outline-primary" @click="redirectToAddClient">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user-cog me-1"></i>
                      Gestionnaire
                    </label>
                    <div class="input-group">
                      <select v-model="renewFormData.gestionnaire_id" class="form-select" required>
                        <option value="" disabled>Sélectionner un gestionnaire</option>
                        <option v-for="gestionnaire in gestionnaires" :key="gestionnaire.id" :value="gestionnaire.id">
                          {{ gestionnaire.nom }} {{ gestionnaire.prenom }}
                        </option>
                      </select>
                      <button type="button" class="btn btn-outline-primary" @click="openAddGestionnaireModal">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-calendar-alt me-2"></i>
                Période & Durée
              </h6>
              <div class="row g-4">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-play me-1"></i>
                      Date de début
                    </label>
                    <input type="date" class="form-control" v-model="renewFormData.date_debut"
                      @change="calculateEndDateForRenew" required />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-clock me-1"></i>
                      Durée
                    </label>
                    <select class="form-select" v-model="renewFormData.duree_mois" @change="calculateEndDateForRenew"
                      required>
                      <option value="" disabled>Sélectionner une durée</option>
                      <option value="3">3 mois</option>
                      <option value="6">6 mois (demi-année)</option>
                      <option value="12">1 an</option>
                      <option value="15">1 an et 3 mois</option>
                      <option value="18">1 an et demi</option>
                      <option value="24">2 ans</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-stop me-1"></i>
                      Date de fin
                    </label>
                    <input type="date" class="form-control" v-model="renewFormData.date_fin" readonly />
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-money-bill-wave me-2"></i>
                Montant & Statut
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-dollar-sign me-1"></i>
                      Montant
                    </label>
                    <div class="input-group">
                      <input type="number" step="0.01" class="form-control" v-model="renewFormData.montant" required />
                      <span class="input-group-text">MAD</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-tasks me-1"></i>
                      Situation
                    </label>
                    <select class="form-select" v-model="renewFormData.situation" required>
                      <option value="DEMANDE">Demande</option>
                      <option value="EN_COURS">En cours</option>
                      <option value="REJETTE">Rejetée</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-title">
                <i class="fas fa-info-circle me-2"></i>
                Informations Complémentaires
              </h6>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-credit-card me-1"></i>
                      Paiement
                    </label>
                    <div class="form-check form-switch payment-switch">
                      <input class="form-check-input" type="checkbox" v-model="renewFormData.payement"
                        id="renewPayementSwitch" />
                      <label class="form-check-label" for="renewPayementSwitch">
                        <span class="switch-text">{{ renewFormData.payement ? "Payé" : "Non payé" }}</span>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-hashtag me-1"></i>
                      Nouvelle référence
                    </label>
                    <input type="text" class="form-control" v-model="renewFormData.reference_numero" maxlength="50"
                      readonly />
                    <small class="form-text">Nouvelle référence générée automatiquement</small>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">
                  <i class="fas fa-sticky-note me-1"></i>
                  Notes
                </label>
                <textarea class="form-control" v-model="renewFormData.notes" rows="3"
                  placeholder="Ajouter des notes..."></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>
            Annuler
          </button>
          <button type="submit" class="btn btn-success" :disabled="isSubmitting" @click="renewDomiciliation">
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="fas fa-sync-alt me-1"></i>
            {{ isSubmitting ? 'Renouvellement en cours...' : 'Renouveler' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Intégration du modal utilisateur -->
  <users-modal ref="usersModal" @user-added="onUserAdded" @user-edited="fetchGestionnaires"></users-modal>

</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import html2pdf from 'html2pdf.js';
import {
  Document,
  Paragraph,
  TextRun,
  AlignmentType,
  Packer,
  Table,
  TableRow,
  TableCell,
  WidthType,
  BorderStyle,
  Footer,
  Header,
  ImageRun,
} from "docx"


const columns = [
  {
    title: "Référence",
    dataIndex: "reference_numero",
    key: "reference",
    sorter: {
      compare: (a, b) => (a.reference_numero || "").localeCompare(b.reference_numero || ""),
    },
    width: 120,
  },
  {
    title: "Client",
    dataIndex: "client_nom",
    key: "client",
    sorter: {
      compare: (a, b) => a.client_nom.localeCompare(b.client_nom),
    },
  },
  {
    title: "Gestionnaire",
    dataIndex: "gestionnaire_nom",
    key: "gestionnaire",
    sorter: {
      compare: (a, b) => a.gestionnaire_nom.localeCompare(b.gestionnaire_nom),
    },
  },
  {
    title: "Date début",
    dataIndex: "date_debut",
    key: "date_debut",
    sorter: {
      compare: (a, b) => new Date(a.date_debut) - new Date(b.date_debut),
    },
    width: 100,
  },
  {
    title: "Date fin",
    dataIndex: "date_fin",
    key: "date_fin",
    sorter: {
      compare: (a, b) => new Date(a.date_fin) - new Date(b.date_fin),
    },
    width: 100,
  },
  {
    title: "Temps restant",
    dataIndex: "time_remaining",
    key: "time_remaining",
    sorter: {
      compare: (a, b) => {
        const daysA = Math.ceil((new Date(a.date_fin) - new Date()) / (1000 * 60 * 60 * 24));
        const daysB = Math.ceil((new Date(b.date_fin) - new Date()) / (1000 * 60 * 60 * 24));
        return daysA - daysB;
      },
    },
    width: 120,
  },
  {
    title: "Durée",
    dataIndex: "duree",
    key: "duree",
    sorter: {
      compare: (a, b) => a.duree_mois - b.duree_mois,
    },
    width: 100,
  },
  {
    title: "Montant",
    dataIndex: "montant",
    key: "montant",
    sorter: {
      compare: (a, b) => a.montant - b.montant,
    },
    width: 100,
  },
  {
    title: "Situation",
    dataIndex: "situation",
    key: "situation",
    sorter: {
      compare: (a, b) => a.situation.localeCompare(b.situation),
    },
    width: 100,
  },
  {
    title: "Paiement",
    dataIndex: "payement",
    key: "payement",
    sorter: {
      compare: (a, b) => (a.payement === b.payement ? 0 : a.payement ? 1 : -1),
    },
    width: 100,
  },
  {
    title: "Action",
    key: "action",
    sorter: false,
    width: 120,
  },
];

export default {
  name: 'DomiciliationManager',
  data() {
    return {
      columns,
      domiciliations: [],
      clients: [],
      gestionnaires: [],
      search: "",
      filters: {
        reference: "",
        client: "",
        situation: "",
        expiration: "",
      },
      pageSize: 10,
      currentPage: 1,
      isSubmitting: false,
      downloadingId: null,
      renewingId: null,

      // Formulaires séparés
      addFormData: {
        client_id: "",
        gestionnaire_id: "",
        date_debut: "",
        date_fin: "",
        duree_mois: "",
        montant: 0,
        situation: "EN_COURS",
        payement: false,
        reference_numero: "",
        notes: "",
      },

      editFormData: {
        client_id: "",
        gestionnaire_id: "",
        date_debut: "",
        date_fin: "",
        duree_mois: "",
        montant: 0,
        situation: "EN_COURS",
        payement: false,
        reference_numero: "",
        notes: "",
      },

      renewFormData: {
        client_id: "",
        gestionnaire_id: "",
        date_debut: "",
        date_fin: "",
        duree_mois: "12", // Par défaut 1 an pour renouvellement
        montant: 0,
        situation: "EN_COURS", // Par défaut en cours pour renouvellement
        payement: false, // Par défaut non payé pour nouveau contrat
        reference_numero: "",
        notes: "",
        previous_id: null,
        renewal_count: 0,
      },

      editId: null,
      originalDomiciliation: null, // Pour afficher les infos de l'ancien contrat
    };
  },

  computed: {
    filteredData() {
      let filtered = [...this.domiciliations];

      // General search
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (d) =>
            (d.client_nom && d.client_nom.toLowerCase().includes(s)) ||
            (d.gestionnaire_nom && d.gestionnaire_nom.toLowerCase().includes(s)) ||
            (d.reference_numero && d.reference_numero.toLowerCase().includes(s)) ||
            (d.situation && d.situation.toLowerCase().includes(s))
        );
      }

      // Specific filters
      if (this.filters.reference) {
        filtered = filtered.filter(d =>
          d.reference_numero && d.reference_numero.toLowerCase().includes(this.filters.reference.toLowerCase())
        );
      }

      if (this.filters.client) {
        filtered = filtered.filter(d => d.client_id === this.filters.client);
      }

      if (this.filters.situation) {
        filtered = filtered.filter(d => d.situation === this.filters.situation);
      }

      if (this.filters.expiration) {
        filtered = filtered.filter(d => {
          const daysLeft = this.getDaysRemaining(d.date_fin);
          switch (this.filters.expiration) {
            case 'expired':
              return daysLeft < 0;
            case 'danger':
              return daysLeft >= 0 && daysLeft <= 5;
            case 'warning':
              return daysLeft > 5 && daysLeft <= 15;
            case 'success':
              return daysLeft > 15;
            default:
              return true;
          }
        });
      }

      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return filtered.slice(start, end);
    },

    filteredGestionnaires() {
      if (this.user?.is_admin) {
        // L'admin voit tous les gestionnaires
        return this.gestionnaires;
      }
      // L'utilisateur normal ne voit que lui-même (basé sur user_id, pas id)
      return this.gestionnaires.filter(g => g.user_id === this.user?.id);
    },

    user() {
      return JSON.parse(localStorage.getItem("user"));
    },


    allFilteredData() {
      let filtered = [...this.domiciliations];

      // General search
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (d) =>
            (d.client_nom && d.client_nom.toLowerCase().includes(s)) ||
            (d.gestionnaire_nom && d.gestionnaire_nom.toLowerCase().includes(s)) ||
            (d.reference_numero && d.reference_numero.toLowerCase().includes(s)) ||
            (d.situation && d.situation.toLowerCase().includes(s))
        );
      }

      // Specific filters
      if (this.filters.reference) {
        filtered = filtered.filter(d =>
          d.reference_numero && d.reference_numero.toLowerCase().includes(this.filters.reference.toLowerCase())
        );
      }

      if (this.filters.client) {
        filtered = filtered.filter(d => d.client_id === this.filters.client);
      }

      if (this.filters.situation) {
        filtered = filtered.filter(d => d.situation === this.filters.situation);
      }

      if (this.filters.expiration) {
        filtered = filtered.filter(d => {
          const daysLeft = this.getDaysRemaining(d.date_fin);
          switch (this.filters.expiration) {
            case 'expired':
              return daysLeft < 0;
            case 'danger':
              return daysLeft >= 0 && daysLeft <= 5;
            case 'warning':
              return daysLeft > 5 && daysLeft <= 15;
            case 'success':
              return daysLeft > 15;
            default:
              return true;
          }
        });
      }

      return filtered;
    },

    totalPages() {
      return Math.ceil(this.allFilteredData.length / this.pageSize);
    },

    startRecord() {
      if (this.allFilteredData.length === 0) return 0;
      return (this.currentPage - 1) * this.pageSize + 1;
    },

    endRecord() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.allFilteredData.length);
    },
  },

  methods: {

    autoSelectGestionnaire() {
      if (this.user && !this.user.is_admin && this.filteredGestionnaires.length > 0) {
        const userGestionnaire = this.filteredGestionnaires[0];
        this.addFormData.gestionnaire_id = userGestionnaire.id;
        this.editFormData.gestionnaire_id = userGestionnaire.id;
        this.renewFormData.gestionnaire_id = userGestionnaire.id;
      } else {
        // facultatif : vider le champ s'il n'a pas le droit
        this.addFormData.gestionnaire_id = "";
      }
    },

    // ==================== API Configuration ====================
    getApiHeaders() {
      const token = localStorage.getItem("access_token");
      return {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
        Accept: "application/json",
      };
    },

    async loadRobotoFont() {
      return new Promise((resolve) => {
        if (document.fonts.check('12px Roboto')) {
          resolve();
          return;
        }

        const link = document.createElement('link');
        link.href = 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap';
        link.rel = 'stylesheet';
        document.head.appendChild(link);

        link.onload = () => {
          document.fonts.ready.then(() => {
            resolve();
          });
        };
      });
    },
    // Méthode pour convertir une image en base64
    async imageToBase64(url) {
      return new Promise((resolve, reject) => {
        const isLocalUrl = url.startsWith('/') || url.startsWith(window.location.origin);
        const img = new Image();

        if (!isLocalUrl) {
          img.crossOrigin = 'anonymous';
        }

        img.onload = function () {
          try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = this.width;
            canvas.height = this.height;
            ctx.drawImage(this, 0, 0);
            const dataURL = canvas.toDataURL('image/png');
            resolve(dataURL);
          } catch (error) {
            console.warn('Erreur lors de la conversion canvas:', error);
            resolve(null);
          }
        };

        img.onerror = (error) => {
          console.warn('Erreur de chargement image:', error);
          resolve(null);
        };

        img.src = url;
      });
    },

    // ==================== Modal Management ====================
    openEditModal(record) {
      this.editId = record.id;
      const duration = this.calculateDurationFromDates(record.date_debut, record.date_fin);

      this.editFormData = {
        client_id: record.client_id,
        gestionnaire_id: record.gestionnaire_id,
        date_debut: record.date_debut,
        date_fin: record.date_fin,
        duree_mois: duration.mois.toString(),
        montant: record.montant,
        situation: record.situation,
        payement: record.payement,
        reference_numero: record.reference_numero || "",
        notes: record.notes || "",
      };
    },

    openRenewModal(record) {
      this.originalDomiciliation = { ...record };
      const today = new Date().toISOString().split('T')[0];

      this.renewFormData = {
        client_id: record.client_id,
        gestionnaire_id: record.gestionnaire_id,
        date_debut: "",
        date_fin: "",
        duree_mois: "12", // Par défaut 1 an
        montant: record.montant, // Reprendre le même montant
        situation: "EN_COURS", // Par défaut en cours
        payement: false, // Nouveau contrat non payé
        reference_numero: this.generateReference(),
        notes: `Renouvellement de ${record.reference_numero}`,
        previous_id: record.id,
        renewal_count: (record.renewal_count || 0) + 1,
      };

      // Calculer automatiquement la date de fin
      // this.calculateEndDateForRenew();
    },

    closeModal(modalId) {
      const modalElement = document.getElementById(modalId);
      if (modalElement) {
        const modal = Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
    },

    // ==================== Form Reset Methods ====================
    resetAddForm() {
      this.addFormData = {
        client_id: "",
        gestionnaire_id: "",
        date_debut: "",
        date_fin: "",
        duree_mois: "",
        montant: 0,
        situation: "DEMANDE",
        payement: false,
        reference_numero: this.generateReference(),
        notes: "",
      };
    },

    resetEditForm() {
      this.editFormData = {
        client_id: "",
        gestionnaire_id: "",
        date_debut: "",
        date_fin: "",
        duree_mois: "",
        montant: 0,
        situation: "DEMANDE",
        payement: false,
        reference_numero: "",
        notes: "",
      };
      this.editId = null;
    },

    resetRenewForm() {
      this.renewFormData = {
        client_id: "",
        gestionnaire_id: "",
        date_debut: "",
        date_fin: "",
        duree_mois: "12",
        montant: 0,
        situation: "EN_COURS",
        payement: false,
        reference_numero: "",
        notes: "",
        previous_id: null,
        renewal_count: 0,
      };
      this.originalDomiciliation = null;
    },

    // ==================== Date Calculation Methods ====================
    calculateEndDateForAdd() {
      if (this.addFormData.date_debut && this.addFormData.duree_mois) {
        const startDate = new Date(this.addFormData.date_debut);
        const endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + parseInt(this.addFormData.duree_mois));
        this.addFormData.date_fin = endDate.toISOString().split("T")[0];
      }
    },

    calculateEndDateForEdit() {
      if (this.editFormData.date_debut && this.editFormData.duree_mois) {
        const startDate = new Date(this.editFormData.date_debut);
        const endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + parseInt(this.editFormData.duree_mois));
        this.editFormData.date_fin = endDate.toISOString().split("T")[0];
      }
    },

    calculateEndDateForRenew() {
      if (this.renewFormData.date_debut && this.renewFormData.duree_mois) {
        const startDate = new Date(this.renewFormData.date_debut);
        const endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + parseInt(this.renewFormData.duree_mois));
        this.renewFormData.date_fin = endDate.toISOString().split("T")[0];
      } else {
        this.renewFormData.date_fin = ""; // ✅ vide si un champ manque
      }
    },

    calculateDurationFromDates(dateDebut, dateFin) {
      if (!dateDebut || !dateFin) return { mois: 0, text: "-" };

      const start = new Date(dateDebut);
      const end = new Date(dateFin);
      const diffTime = Math.abs(end - start);
      const diffMonths = Math.round(diffTime / (1000 * 60 * 60 * 24 * 30.44));

      return {
        mois: diffMonths,
        text: this.formatDurationText(diffMonths),
      };
    },

    // ==================== CRUD Operations ====================

    // CREATE - Ajouter une nouvelle domiciliation
    async saveDomiciliation() {
      try {
        this.isSubmitting = true;
        const user = JSON.parse(localStorage.getItem("user"));
        console.log(user.is_admin);
        const token = localStorage.getItem("access_token");

        if (!token) {
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
          return;
        }

        if (!this.addFormData.reference_numero) {
          this.addFormData.reference_numero = this.generateReference();
        }

        const payload = {
          ...this.addFormData,
          created_by: user.id,
          is_deleted: false,
          renewal_count: 0,
        };

        const response = await axios.post("/domiciliations", payload, {
          headers: this.getApiHeaders(),
        });

        if (response.data && response.data.data) {
          const domiciliationId = response.data.data.domiciliation_id;
          console.log("Domiciliation créée:", domiciliationId);

          this.showSuccessMessage("Domiciliation créée avec succès");
          this.resetForm();
          this.closeModal("add-domiciliation");
          await this.fetchDomiciliations();

          // Ask user which format they want to download
          const result = await Swal.fire({
            title: "Télécharger le contrat",
            text: "Quel format souhaitez-vous télécharger ?",
            icon: "question",
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: "PDF",
            denyButtonText: "Word",
            cancelButtonText: "Plus tard",
            confirmButtonColor: "#dc3545",
            denyButtonColor: "#0d6efd",
          });

          if (result.isConfirmed) {
            // Download PDF
            this.downloadContractPdf(domiciliationId);
          } else if (result.isDenied) {
            this.downloadContractWord(domiciliationId);
          }
        }
      } catch (error) {
        console.error("Erreur création:", error);
        this.handleApiError(error);
      } finally {
        this.isSubmitting = false;
      }
    },

async downloadContractPdf(id) {
      try {
        const response = await axios.get(`/generate-contract-pdf/${id}`);
        const { htmlContract1, htmlContract2 } = response.data;

        // Step 1: Open a new tab/window
        const newWindow = window.open("", "_blank");

        if (!newWindow) {
          alert("Popup blocked. Please allow pop-ups for PDF preview.");
          return;
        }

        // Step 2: Create a combined HTML with both contracts
        const combinedHtml = `
      <!DOCTYPE html>
      <html>
        <head>
          <title>Contracts Preview</title>
          <style>
            body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
            .contract-container { margin-bottom: 40px; border: 1px solid #ddd; padding: 20px; }
            .contract-title { font-weight: bold; margin-bottom: 20px; font-size: 18px; }
          </style>
        </head>
        <body>
          <div class="contract-container">
            <div class="contract-title">Contract 1</div>
            <div id="contract1-content">${htmlContract1}</div>
          </div>
          <div class="contract-container">
            <div class="contract-title">Contract 2</div>
            <div id="contract2-content">${htmlContract2}</div>
          </div>
        </body>
      </html>
    `;

        // Step 3: Inject the combined HTML into the window
        newWindow.document.open();
        newWindow.document.write(combinedHtml);
        newWindow.document.close();

        // Step 4: Wait for the window to load and generate separate PDFs
        newWindow.onload = async () => {
          setTimeout(async () => {
            try {
              // Helper function to generate PDF from a specific element
              const generatePdfFromElement = async (elementId, filename) => {
                const element = newWindow.document.getElementById(elementId);
                if (!element) {
                  console.error(`Element ${elementId} not found`);
                  return;
                }

                // Clone the element
                const clone = element.cloneNode(true);

                // Create hidden container in current window
                const container = document.createElement("div");
                container.style.position = "fixed";
                container.style.left = "-9999px";
                container.style.top = "0";
                container.style.width = "1000px";
                container.style.zIndex = "-1";
                container.appendChild(clone);
                document.body.appendChild(container);

                // Generate PDF
                await html2pdf()
                  .from(clone)
                  .set({
                    margin: [15, 5, 20, 5],
                    html2canvas: {
                      scale: 2,
                      useCORS: true,
                      letterRendering: true,
                      dpi: 300,
                    },
                    image: { type: "png", quality: 1 },
                    jsPDF: {
                      unit: "mm",
                      format: "letter",
                      orientation: "portrait",
                    },
                    filename: filename,
                  })
                  .save();

                // Cleanup
                document.body.removeChild(container);

                // Small delay between downloads
                await new Promise(resolve => setTimeout(resolve, 500));
              };

              // Generate both PDFs
              await generatePdfFromElement('contract1-content', `domiciliation_contract_1_${id}.pdf`);
              await generatePdfFromElement('contract2-content', `domiciliation_contract_2_${id}.pdf`);

              // Close the window after both PDFs are generated
              newWindow.close();
            } catch (err) {
              console.error("PDF generation failed:", err);
              newWindow.close();
            }
          }, 1000); // Increased delay to ensure content is fully rendered
        };
      } catch (error) {
        console.error("Error generating contract PDF:", error);
      }
    },

    async downloadContractWord(id) {
      try {
        const response = await axios.get(`/generate-contract-word/${id}`)
        const data1 = response.data.data1

        const societe = data1.societe || {}
        const domiciliation = data1.domiciliation || {}
        const client = data1.client || {}
        const gerant = data1.gerant || {}
        const periodeMonths = data1.periodeMonths || 0
        const periodeArabe = data1.periodeArabe

        const getImageAsBase64 = async (imageUrl, width = 150, height = 56) => {
          if (!imageUrl) {
            console.log("No image URL provided");
            return null;
          }

          try {
            // Detect SVG by extension or content-type
            if (imageUrl.endsWith('.svg') || imageUrl.includes('image/svg+xml')) {
              // Fetch SVG as text
              const response = await fetch(imageUrl);
              if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
              const svgText = await response.text();

              // Convert SVG to PNG using canvas
              return await new Promise((resolve, reject) => {
                const img = new window.Image();
                img.crossOrigin = "anonymous";
                const svgBlob = new Blob([svgText], { type: "image/svg+xml" });
                const url = URL.createObjectURL(svgBlob);
                img.onload = function () {
                  try {
                    const canvas = document.createElement("canvas");
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0, width, height);
                    const pngBase64 = canvas.toDataURL("image/png").split(",")[1];
                    URL.revokeObjectURL(url);
                    resolve(pngBase64);
                  } catch (err) {
                    URL.revokeObjectURL(url);
                    reject(err);
                  }
                };
                img.onerror = (e) => {
                  URL.revokeObjectURL(url);
                  reject(new Error("Failed to load SVG image"));
                };
                img.src = url;
              });
            } else {
              // For PNG, JPG, etc.
              const response = await fetch(imageUrl, {
                method: "GET",
                mode: "cors",
                credentials: "omit",
                headers: { Accept: "image/*" },
              });
              if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
              const blob = await response.blob();
              if (blob.size === 0) throw new Error("Empty image blob received");

              return await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => {
                  try {
                    const result = reader.result;
                    if (!result || typeof result !== "string") {
                      reject(new Error("Failed to read image as data URL"));
                      return;
                    }
                    const base64String = result.split(",")[1];
                    if (!base64String) {
                      reject(new Error("Invalid data URL format"));
                      return;
                    }
                    resolve(base64String);
                  } catch (error) {
                    reject(error);
                  }
                };
                reader.onerror = () => reject(new Error("FileReader error"));
                reader.readAsDataURL(blob);
              });
            }
          } catch (error) {
            console.error("Error converting image to base64:", error);
            console.error("Failed URL:", imageUrl);
            return null;
          }
        };


        // Enhanced function to get proper image URL
        async function getImageUrl(imagePath) {
          if (!imagePath) {
            console.log("No image path provided")
            return null
          }

          console.log("Processing image path:", imagePath)

          // If it's already a full URL, return as-is
          if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
            console.log("Using full URL:", imagePath)
            return imagePath
          }

          // Try different URL patterns
          const baseUrls = [
            "http://localhost:8000/api/storage/",
          ]

          for (const baseUrl of baseUrls) {
            const testUrl = baseUrl + imagePath.replace(/^\/+/, "") // Remove leading slashes
            console.log("Testing URL:", testUrl)

            try {
              const response = await fetch(testUrl, {
                method: "HEAD",
                mode: "cors",
                credentials: "omit",
              })

              if (response.ok) {
                console.log("Found working URL:", testUrl)
                return testUrl
              }
            } catch (error) {
              console.log("URL failed:", testUrl, error.message)
            }
          }

          console.log("No working URL found for image path:", imagePath)
          return null
        }

        const generateWordDoc = async (
          societe,
          domiciliation,
          client,
          gerant,
          periodeArabe,
          periodeMonths,
          templateType,
          filename,
        ) => {
          let doc

          // Get logo as base64 if available
          console.log("Starting logo processing...")
          console.log("Societe logo path:", societe.logo)

          let logoBase64 = null

          // Try to get company logo first
          if (societe.logo) {
            const logoUrl = await getImageUrl(societe.logo)
            if (logoUrl) {
              logoBase64 = await getImageAsBase64(logoUrl)
            }
          }

          // Fallback to default logo if company logo failed
          if (!logoBase64) {
            console.log("Trying fallback logo...")
            const fallbackUrl = "https://meconsulting.ma/wp-content/uploads/2023/12/Mansouri_Logo.svg"
            logoBase64 = await getImageAsBase64(fallbackUrl)
          }

          if (logoBase64) {
            console.log("Logo successfully converted to base64")
          } else {
            console.log("No logo will be included in the document")
          }

          if (templateType === "main") {
            // Calculate period information
            const startDate = new Date(domiciliation?.date_debut)
            const endDate = new Date(domiciliation?.date_fin)
            const diffTime = Math.abs(endDate.getTime() - startDate.getTime())

            const dateDebut = startDate.toLocaleDateString("ar-MA")
            const dateFin = endDate.toLocaleDateString("ar-MA")
            const currentDate = new Date().toLocaleDateString("ar-MA")
            const montant = domiciliation?.montant ? new Intl.NumberFormat().format(domiciliation.montant) : "غير محدد"
            const montantMensuel = domiciliation?.montant
              ? new Intl.NumberFormat().format((domiciliation.montant / periodeMonths).toFixed(2))
              : "غير محدد"

            // Create header children array
            const headerChildren = []

            // Add logo if available - positioned in top left
            if (logoBase64) {
              try {
                headerChildren.push(
                  new Paragraph({
                    children: [
                      new ImageRun({
                        data: logoBase64,
                        transformation: {
                          width: 150, // pixels
                          height: 56, // pixels
                        }
                      }),
                    ],
                    alignment: AlignmentType.RIGHT, // Not necessary with floating image, but ok to keep
                  })
                );
                console.log("Logo added to header successfully")
              } catch (error) {
                console.error("Error adding logo to document header:", error)
              }
            }

            doc = new Document({
              sections: [
                {
                  properties: {
                    page: {
                      margin: {
                        top: 1440, // Increased top margin to accommodate logo
                        right: 720,
                        bottom: 1440,
                        left: 720,
                      },
                    },
                  },
                  headers: {
                    default: new Header({
                      children:
                        headerChildren.length > 0
                          ? headerChildren
                          : [
                            new Paragraph({
                              children: [new TextRun({ text: "" })], // Empty paragraph if no logo
                            }),
                          ],
                    }),
                  },
                  footers: {
                    default: new Footer({
                      children: [
                        new Paragraph({
                          children: [
                            new TextRun({
                              text: `ICE: ${societe.ice || "غير محدد"} TP: ${societe.tp || "غير محدد"} IF: ${societe.identifiant_fiscal || "غير محدد"} RC: ${societe.rc || "غير محدد"} SIÈGE SOCIAL: ${societe.adresse_siege_social_francais || "غير محدد"} Tél: ${societe.telephone || "غير محدد"}`,
                              size: 14,
                              color: "666666",
                            }),
                          ],
                          alignment: AlignmentType.CENTER,
                          spacing: { before: 200 },
                        }),
                      ],
                    }),
                  },
                  children: [
                    // Title
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `${domiciliation.renewal_count > 0 ? 'تجديد' : ''} عقد توطين`,
                          bold: true,
                          size: 48,
                          underline: {},
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.CENTER,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // Subtitle
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "طبقا لنموذج المرسوم رقم 2.20.950 الصادر في 26 يونيو 2021 بتطبيق المادتين 544-2 و544-7 من القانون رقم 15.95 المتعلق بمدونة التجارة",
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.CENTER,
                      spacing: { after: 600 },
                      rightToLeft: true,
                    }),

                    // بين المتعاقدين
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "بين المتعاقدين",
                          bold: true,
                          underline: {},
                          size: 30,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.CENTER,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // الموطن لديه
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "الموطن لديه",
                          bold: true,
                          underline: {},
                          size: 24,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    // Combined societe information in one paragraph
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `كشخص اعتباري شركة ${societe?.societe_nom || societe?.nom_francais || "غير محدد"} شركة ذات المسؤولية المحدودة، رأسمالها الاجتماعي ${societe?.capital_social ? new Intl.NumberFormat().format(societe.capital_social) : "غير محدد"} درهم، رقم التعريف الموحد للمقاولات ${societe?.ice || "غير محدد"} رقم الضريبة المهنية ${societe?.tp || "غير محدد"} رقم تعريفها الجبائي ${societe?.identifiant_fiscal || "غير محدد"} المقيدة بالسجل التجاري رقم ${societe?.rc || "غير محدد"} التابع للمحكمة التجارية، مقرها الاجتماعي ${societe?.adresse_siege_social_arabe || "غير محدد"}. ممثلة من طرف السيد ${societe?.nom_complet_arabe || "غير محدد"}، المزداد بتاريخ ${societe?.president_date_naissance || "غير محدد"} بالرباط، الحامل لبطاقة التعريف الوطنية عدد ${societe?.president_cin || "غير محدد"}، القاطن ${societe?.president_adresse || "غير محدد"}.`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // والموطن
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "والموطن",
                          bold: true,
                          underline: {},
                          size: 24,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    // Combined client information in one paragraph
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `الشركة في طور التأسيس المسماة ${client?.nom_francais || client?.nom || "غير محدد"} رقم التعريف الموحد للمقاولات ${client?.ice || "غير محدد"} ممثلة من طرف السيد ${gerant?.nom || "غير محدد"}، المزداد سنة ${gerant?.date_naissance || "غير محدد"}، الحامل لبطاقة التعريف الوطنية ${gerant?.cin || "غير محدد"}، القاطن ب ${gerant?.address || gerant?.adresse || "غير محدد"}.`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // تم الاتفاق على ما يلي
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "تم الاتفاق على ما يلي :",
                          bold: true,
                          underline: {},
                          size: 24,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // المادة 1 - الموضوع
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "المادة 1 - الموضوع",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `يهدف العقد إلى توطين مقر الموطن ${client?.type_entreprise || "الشركة"} في طور التأسيس المسماة: ${client?.nom_francais || client?.nom || "غير محدد"} رقم التعريف الموحد للمقاولات ${client?.ice || "غير محدد"} تطبيقا لمقتضيات المادة 544-2 من القانون رقم 15.95 المتعلق بمدونة التجارة.`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة 2 - الخدمات
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "المادة 2 - الخدمات",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "يلتزم الموطن لديه بتمكين الشخص الموطن المشار إليه في المادة الأولى أعلاه من الاستفادة من الخدمات التالية:",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    // Services list
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "- استعمال عنوان الموطن لديه كعنوان للمقر الاجتماعي للشخص الموطن.\n- وضع رهن إشارته محلا أو محلات مجهزة بوسائل الاتصال مخصصة للمكاتب.\n- محلات معدة لمسك السجلات والوثائق المنصوص عليها في النصوص التشريعية والتنظيمية الجاري بها العمل وتمكن من حفظها والاطلاع عليها مع الالتزام بتحيينها.\n- استلام وتخزين وإعادة إرسال البريد اليومي.\n- استلام الفاكس.\n- تلقي المكالمات الهاتفية.\n- استلام الطرود.",
                          size: 16,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة 3 - الالتزامات
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "المادة 3 - الالتزامات",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    // 1 – التزامات الموطن لديه
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": أولا - التزامات الموطن لديه",
                          bold: true,
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 100 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ":طوال مدة العقد يجب على الموطن لديه التقيد بالالتزامات التالية",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    // All obligations for الموطن لديه
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "- مسك ملف عن كل شخص موطن يحتوي على وثائق الإثبات تتعلق فيما يخص الأشخاص الذاتيين بعناوينهم الشخصية وأرقام هواتفهم وأرقام بطاقات هويتهم وكذا عناوين بريدهم الالكتروني وفيما يخص الأشخاص الاعتباريين وثائق تثبت عناوين وأرقام هواتف وبطاقات هوية مسيريها وكذا عناوين بريدهم الالكتروني، كما يحتوي كذلك على وثائق تتعلق بجميع محلات نشاط المقاولات الموطنة ومكان حفظ الوثائق المحاسباتية في حال عدم حفظها لدى الموطن لديه.\n- التأكد من أن الموطن مسجل في السجل التجاري داخل أجل ثلاثة أشهر من تاريخ إبرام عقد التوطين عندما يكون هذا التسجيل إجباريا بموجب النصوص التشريعية والتنظيمية الجاري بها العمل.\n- موافاة المصالح المكلفة بالضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك بلائحة الأشخاص الموطنين خلال السنة المنصرمة وذلك قبل 31 يناير من كل سنة.\n- إخبار كاتب الضبط لدى المحكمة المختصة ومصالح الضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك بانتهاء مدة عقد التوطين أو الفسخ المبكر له وذلك داخل أجل شهر من تاريخ توقف العقد.\n- تمكين المفوضين القضائيين ومصالح تحصيل الديون العمومية، الحاملين لسند تنفيذي، من المعلومات الكفيلة بتمكينهم من الاتصال بالشخص الموطن.\n- السهر على احترام سرية المعلومات والبيانات المتعلقة بالموطن.\n- إشعار مصالح الضرائب والخزينة العامة وعند الاقتضاء إدارة الجمارك داخل أجل لا يتعدى 15 خمسة عشر يوما من تاريخ توصله بالرسائل المضمونة المرسلة من قبل المصالح الجبائية إلى الأشخاص الموطنين بتعذر تسليمها إليهم.\n- تحمل المسؤولية التضامنية في أداء الضرائب والرسوم المتعلقة بالنشاط الممارس من طرف الموطن طبقا لأحكام الفقرة الأخيرة من المادة 544-2 من القانون رقم 15.95 المتعلق بمدونة التجارة.",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    // 2-التزامات الشخص الذاتي او الاعتباري الموطن
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `: ثانيا - التزامات الشخص الموطن`,
                          bold: true,
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 100 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": يجب على الشخص الموطن طيلة مدة العقد التقيد بالالتزامات التالية",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    // All obligations for الشخص الموطن
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "- الاستعمال الفعلي والحصري للمحلات كمقر للمقاولة أو للشركة أو إذا كان المقر بالخارج كوكالة أو فرع أو تمثيلية أو أي مؤسسة تابعة لها كيفما كانت طبيعتها.\n- التصريح لدى الموطن لديه، إذا تعلق الأمر بشخص ذاتي، بكل تغيير في عنوانه الشخصي ونشاطه وإذا تعلق الأمر بشخص اعتباري، التصريح بكل تغيير في شكله القانوني وتسميته وغرضه، وكذا أسماء وعناوين المسيرين والأشخاص الذين يتوفرون على تفويض من الموطن للتعاقد باسمه مع الموطن لديه وتسليمه الوثائق المتعلقة بذلك.\n- إخبار كاتب الضبط لدى المحكمة المختصة ومصالح الضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك، بتوقف التوطين وذلك داخل أجل شهر من تاريخ انتهاء مدة العقد أو فسخه المبكر له و ذلك داخل أجل شهر من توقفه حتى تتمكن الإدارات المذكورة من اتخاذ الإجراءات المناسبة وترتيب الجزاءات القانونية اللازمة و لأجله وإثر انصرام ثلاثة أجال نصف سنوية كأقصى مدة لتجديد عقد التوطين دون القيام بذلك يعد العقد متوقفا ويحق للشركة فسخه أحاديا في الشهر الموالي مع إعلام الإدارات المذكورة وذلك دون سابق إعلام",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // 3-ملف التوطين
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": ثالثا - ملف التوطين ",
                          bold: true,
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 100 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "يودع الشخص الموطن لدى الموطن لديه ملف التوطين، والذي يتكون من الوثائق التالية: - نسخة من وثيقة هوية الممثل القانوني للمقاولة الموطنة. - نسخة من وثيقة تثبت موطن الممثل القانوني للمقاولة الموطنة. - كشف الحساب البنكي أو الشيك. - رقم هاتف الممثل القانوني للمقاولة الموطنة. - عنوان إعادة إرسال البريد. - نسخة من النظام الأساسي. يلتزم الشخص الموطن بإشعار مصالح الضرائب والخزينة العامة للمملكة، وعند الاقتضاء إدارة الجمارك، بكل تغيير يمس إحدى وثائق الملف، داخل أجل 15 خمسة عشر يوما من علمه بهذا التغيير وكذا بمكان تخزين السلع المستوردة أو الموجهة للتصدير.",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة 4 - المدة
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "المادة 4 - المدة",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `يبرم عقد التوطين لمدة ${periodeArabe} ويبتدئ تاريخها ${dateDebut} إلى ${dateFin}. عند انتهاء مدة عقد التوطين أو فسخه المبكر، يلتزم الموطن لديه والموطن بإخبار كاتب ضبط المحكمة التجارية ومصالح الضرائب والخزينة العامة للمملكة بتوقف التوطين داخل أجل شهر من توقف العقد.`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة 5 - الإيجار
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "المادة 5 - الإيجار",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `يبرم عقد التوطين مقابل إيجار شهري قدره ${montantMensuel} درهم ويؤدى مجموعها أي مبلغ ${montant} درهم كل ${periodeMonths} أشهر وذلك مقابل التوطين مع الخدمات المشار إليها في المادة 2 أعلاه.`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "المادة 6 : اختصاص المحاكم",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `.يعرض كل نزاع قد ينشأ بين الطرفين أثناء تنفيذ العقد على المحكمة المختصة لمقر الموطن لديه`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "المادة 7 : اختيار الموطن",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `.يحدد الأطراف مقر الشركة الموطن لديها كموكن مختار`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // Date and place
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `${currentDate} بتاريخ  في أربع نسخ  ${client.ville} وحرر في`,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // Signatures Table
                    new Table({
                      rows: [
                        new TableRow({
                          children: [
                            new TableCell({
                              children: [
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: "توقيع الموطن لديه",
                                      bold: true,
                                      size: 20,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 100 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: societe?.president_nom || societe?.nom_complet_arabe || "غير محدد",
                                      size: 22,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 200 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: "___________________ : التوقيع",
                                      size: 22,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  rightToLeft: true,
                                }),
                              ],
                              width: {
                                size: 50,
                                type: WidthType.PERCENTAGE,
                              },
                              borders: {
                                top: { style: BorderStyle.SINGLE, size: 1 },
                                bottom: { style: BorderStyle.SINGLE, size: 1 },
                                left: { style: BorderStyle.SINGLE, size: 1 },
                                right: { style: BorderStyle.SINGLE, size: 1 },
                              },
                            }),
                            new TableCell({
                              children: [
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: "توقيع ممثل الموطن",
                                      bold: true,
                                      size: 20,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 100 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: `${gerant?.nom || ""}`.trim() || "غير محدد",
                                      size: 22,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 200 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: "___________________ : التوقيع",
                                      size: 22,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  rightToLeft: true,
                                }),
                              ],
                              width: {
                                size: 50,
                                type: WidthType.PERCENTAGE,
                              },
                              borders: {
                                top: { style: BorderStyle.SINGLE, size: 1 },
                                bottom: { style: BorderStyle.SINGLE, size: 1 },
                                left: { style: BorderStyle.SINGLE, size: 1 },
                                right: { style: BorderStyle.SINGLE, size: 1 },
                              },
                            }),
                          ],
                        }),
                      ],
                      width: {
                        size: 100,
                        type: WidthType.PERCENTAGE,
                      },
                    }),
                  ],
                },
              ],
            })
          } else {
            // Supplement document with same logo fixes
            const supplementHeaderChildren = []

            // Add logo if available - positioned in top left
            if (logoBase64) {
              try {
                supplementHeaderChildren.push(
                  new Paragraph({
                    children: [
                      new ImageRun({
                        data: logoBase64,
                        transformation: {
                          width: 150, // pixels
                          height: 56, // pixels
                        }
                      }),
                    ],
                    alignment: AlignmentType.RIGHT, // Not necessary with floating image, but ok to keep
                  })
                );
                console.log("Logo added to header successfully")
              } catch (error) {
                console.error("Error adding logo to document header:", error)
              }
            }

            doc = new Document({
              sections: [
                {
                  properties: {
                    page: {
                      margin: {
                        top: 1440, // Increased top margin to accommodate logo
                        right: 720,
                        bottom: 1440,
                        left: 720,
                      },
                    },
                  },
                  headers: {
                    default: new Header({
                      children:
                        supplementHeaderChildren.length > 0
                          ? supplementHeaderChildren
                          : [
                            new Paragraph({
                              children: [new TextRun({ text: "" })], // Empty paragraph if no logo
                            }),
                          ],
                    }),
                  },
                  footers: {
                    default: new Footer({
                      children: [
                        new Paragraph({
                          children: [
                            new TextRun({
                              text: `ICE: ${societe.ice || "غير محدد"} TP: ${societe.tp || "غير محدد"} IF: ${societe.identifiant_fiscal || "غير محدد"} RC: ${societe.rc || "غير محدد"} SIÈGE SOCIAL: ${societe.adresse_siege_social_francais || "غير محدد"} Tél: ${societe.telephone || "غير محدد"}`,
                              size: 14,
                              color: "666666",
                            }),
                          ],
                          alignment: AlignmentType.CENTER,
                          spacing: { before: 200 },
                        }),
                      ],
                    }),
                  },
                  children: [
                    // Title
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `${domiciliation.renewal_count > 0 ? 'تجديد' : ''} ملــــحق لعقـــــــد التوطـــــــين`,
                          bold: true,
                          size: 32,
                          underline: {},
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.CENTER,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // Subtitle
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ".يحدد هذا الملحق شروطا و التزامات للطرفين تعتبر جزءا لا يتجرأ من العقد الأصلي و ذلك طبقا للقانون 15.95 المتعلق بالتوطين",
                          underline: {},
                          size: 20,
                        }),
                      ],
                      alignment: AlignmentType.CENTER,
                      spacing: { after: 600 },
                      rightToLeft: true,
                    }),

                    // المادة الأولى
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": المادة الأولى",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ".بمقتضى الفقرة 5 من المادة 544-4 من القانون 15.95 يتعين على الشركات والأشخاص الذاتيين التصريح للموطنة لديها بمكان حفظ الوثائق المحاسباتية وبجميع محلات نشاط المقاولة و مدها بنسخ من الأنظمة الأساسية و مستخلص السجل التجاري في حالة عدم قيام المسيرين بذلك يحق للشركة فسخ العقد أحاديا قبل انتهاء مدته و الرجوع عليهم في حالة المسؤولية التضامنية المنصوص عليها بمقتضى الفقرة الأخيرة من نفس المادة",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة الثانية
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": المادة الثانية",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ".بمقتضى هذه المادة وطبقا لمقتضيات الفقرة 5 من الفصل 544-6 يمنح الموطن للموطنة لديه وكالة خاصة لاستلام كل التبليغات و المراسلات الإدارية وغيرها باسمه",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة الثالثة
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": المادة الثالثة",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ".تطبيقا لمقتضيات الفقرة 9 من المادة 544-4 فإن الموطن لديها ملزمة بإخبار كتابة ضبط المحكمة التجارية بالرباط و المديرية الجهوية للضرائب بالرباط والخزينة العامة للمملكة وإدارة الجمارك عند الاقتضاء بانتهاء عقد التوطين نظرا لانصرام مدته أو الفسخ المبكر له و ذلك داخل أجل شهر من توقفه حتى تتمكن الإدارات المذكورة من اتخاذ الإجراءات المناسبة وترتيب الجزاءات القانونية اللازمة و لأجله وإثر انصرام ثلاثة أجال نصف سنوية كأقصى مدة لتجديد عقد التوطين دون القيام بذلك يعد العقد متوقفا ويحق للشركة فسخه أحاديا في الشهر الموالي مع إعلام الإدارات المذكورة وذلك دون سابق إعلام",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة الرابعة
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": المادة الرابعة",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ".إضافة الى حالة عدم تجديد العقد يحق للموطنة لديها فسخ عقود التوطين أحاديا ولو قبل انتهاء مدتها في حالات تعذر الاتصال بالمسيرين و الأشخاص الذاتيين أو رفض تجديد العقد أو حالات الاخلال بواجباتهم الضريبية و الاداءات للصندوق الوطني لضمان الاجتماعي أو ارتكاب مخالفات و جنح جمركية أو الاستعمالات لأغراض غير مشروعة",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ".لا تعد الموطن لديها ولا يعد مسيروها مسؤولين عن المقر الاجتماعي المشترك و تخلى ذمتهم من أية مسؤولية مدنية كانت او جنائية في حالة  القوة القاهرة أو فعل السلطة أو لصدور أحكام القضاء أو لتغيير في الأنظمة والقوانين",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // المادة السادسة
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ": المادة السادسة",
                          bold: true,
                          underline: {},
                          size: 22,
                          color: "000000",
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 200 },
                      rightToLeft: true,
                    }),

                    new Paragraph({
                      children: [
                        new TextRun({
                          text: ".يعرض كل نزاع بـسبب تنفيذ مقتضيات عقد التوطين او هذا الملحق على محاكم الرباط المختصة محليا",
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.LEFT,
                      spacing: { after: 300 },
                      rightToLeft: true,
                    }),

                    // Date
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `وحرر في نسختين بالرباط بتاريخ ${new Date().toLocaleDateString("ar-MA")}`,
                          bold: true,
                          size: 22,
                        }),
                      ],
                      alignment: AlignmentType.CENTER,
                      spacing: { after: 400 },
                      rightToLeft: true,
                    }),

                    // Signatures Table
                    new Table({
                      rows: [
                        new TableRow({
                          children: [
                            new TableCell({
                              children: [
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: ": توقيع الموطن لديه",
                                      bold: true,
                                      size: 20,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 100 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: `${societe.nom_complet_francais || "مراد المنصوري"}  ممثلة من طرف السيد ${societe.societe_nom || "MA EXPERTISE CONSULTING"}`,
                                      size: 16,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 200 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: "___________________ : التوقيع",
                                      size: 22,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  rightToLeft: true,
                                }),
                              ],
                              width: {
                                size: 50,
                                type: WidthType.PERCENTAGE,
                              },
                              borders: {
                                top: { style: BorderStyle.SINGLE, size: 1 },
                                bottom: { style: BorderStyle.SINGLE, size: 1 },
                                left: { style: BorderStyle.SINGLE, size: 1 },
                                right: { style: BorderStyle.SINGLE, size: 1 },
                              },
                            }),
                            new TableCell({
                              children: [
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: ": توقيع ممثل الموطن",
                                      bold: true,
                                      size: 20,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 100 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: `${gerant?.nom ?? "-"} ممثلة من طرف السيد ${client?.nom_francais ?? "-"}`,
                                      size: 16,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  spacing: { after: 200 },
                                  rightToLeft: true,
                                }),
                                new Paragraph({
                                  children: [
                                    new TextRun({
                                      text: "___________________ : التوقيع",
                                      size: 22,
                                    }),
                                  ],
                                  alignment: AlignmentType.CENTER,
                                  rightToLeft: true,
                                }),
                              ],
                              width: {
                                size: 50,
                                type: WidthType.PERCENTAGE,
                              },
                              borders: {
                                top: { style: BorderStyle.SINGLE, size: 1 },
                                bottom: { style: BorderStyle.SINGLE, size: 1 },
                                left: { style: BorderStyle.SINGLE, size: 1 },
                                right: { style: BorderStyle.SINGLE, size: 1 },
                              },
                            }),
                          ],
                        }),
                      ],
                      width: {
                        size: 100,
                        type: WidthType.PERCENTAGE,
                      },
                    }),
                  ],
                },
              ],
            })
          }

          // Generate and download the document using toBlob()
          console.log("Generating Word document...")
          const blob = await Packer.toBlob(doc)
          console.log("Document generated successfully, size:", blob.size)

          // Create download link
          const link = document.createElement("a")
          link.href = URL.createObjectURL(blob)
          link.download = filename
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)
          URL.revokeObjectURL(link.href)

          console.log("Document download initiated:", filename)
        }

        console.log("Starting document generation process...")

        // Generate both documents
        await generateWordDoc(
          societe,
          domiciliation,
          client,
          gerant,
          periodeArabe,
          periodeMonths,
          "main",
          `domiciliation_contract_${id}.docx`,
        )
        await generateWordDoc(
          societe,
          domiciliation,
          client,
          gerant,
          periodeArabe,
          periodeMonths,
          "supplement",
          `domiciliation_supplement_${id}.docx`,
        )

        console.log("All documents generated successfully")
      } catch (error) {
        console.error("Error generating contract Word documents:", error)
        throw error
      }
    },

    async downloadContract(record) {
      this.downloadingId = record.id;
      this.isDownloading = true;

      try {
        const result = await Swal.fire({
          title: "Télécharger le contrat",
          text: "Quel format souhaitez-vous télécharger ?",
          icon: "question",
          showCancelButton: true,
          showDenyButton: true,
          confirmButtonText: "PDF",
          denyButtonText: "Word",
          cancelButtonText: "Plus tard",
          confirmButtonColor: "#dc3545",
          denyButtonColor: "#0d6efd",
        });

        if (result.isConfirmed) {
          await this.downloadContractPdf(record.id);
        } else if (result.isDenied) {
          await this.downloadContractWord(record.id);
        }

      } catch (error) {
        console.error("Erreur lors du téléchargement :", error);
      } finally {
        this.isDownloading = false;
        this.downloadingId = null;
      }
    },

    // UPDATE - Modifier une domiciliation existante
    async updateDomiciliation() {
      try {
        this.isSubmitting = true;
        const user = JSON.parse(localStorage.getItem("user"));
        const token = localStorage.getItem("access_token");

        if (!token) {
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
          return;
        }

        const payload = {
          ...this.editFormData,
          updated_by: user.id,
        };

        const response = await axios.put(`/domiciliations/${this.editId}`, payload, {
          headers: this.getApiHeaders(),
        });

        if (response.data && response.data.data) {
          this.showSuccessMessage("Domiciliation mise à jour avec succès");
          this.resetEditForm();
          this.closeModal("edit-domiciliation");
          await this.fetchDomiciliations();
        }
      } catch (error) {
        console.error("Erreur lors de la mise à jour de la domiciliation:", error);
        this.handleApiError(error);
      } finally {
        this.isSubmitting = false;
      }
    },

    // RENEW - Renouveler une domiciliation (créer nouveau + archiver ancien)
    async renewDomiciliation() {
      try {
        this.isSubmitting = true;
        const user = JSON.parse(localStorage.getItem("user"));
        const token = localStorage.getItem("access_token");

        if (!token) {
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
          return;
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
          return;
        }

        // Préparer les données pour le nouveau contrat
        const renewalPayload = {
          ...this.renewFormData,
          created_by: user.id,
          is_deleted: false,
          is_renewal: true,
        };

        // Créer le nouveau contrat
        const response = await axios.post('/domiciliations', renewalPayload, {
          headers: this.getApiHeaders()
        });

        if (response.data?.data) {
          // Marquer l'ancien contrat comme renouvelé
          await axios.post(`/domiciliations/${this.renewFormData.previous_id}/renew`, {
            renewed_by: user.id,
            new_domiciliation_id: response.data.data.id,
            renewed_at: new Date().toISOString()
          }, {
            headers: this.getApiHeaders()
          });

          this.showSuccessMessage("Domiciliation renouvelée avec succès ! L'ancien contrat a été archivé.");
          this.resetRenewForm();
          this.closeModal("renew-domiciliation");
          await this.fetchDomiciliations();
        }
      } catch (error) {
        console.error("Erreur lors du renouvellement:", error);
        if (error.response) {
          console.error("Status:", error.response.status);
          console.error("Data:", error.response.data);
          this.showErrorMessage(error.response.data?.error || error.message);
        } else {
          this.showErrorMessage("Erreur inconnue lors du renouvellement");
        }
        this.handleApiError(error);
      } finally {
        this.isSubmitting = false;
      }
    },

    // DELETE
    async onDeleteDomiciliation(record) {
      const { value: raison } = await Swal.fire({
        title: "Confirmer la résiliation",
        input: 'text',
        inputLabel: 'Raison de la résiliation',
        inputPlaceholder: 'Entrez la raison...',
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Confirmer la résiliation",
        cancelButtonText: "Annuler",
        inputValidator: (value) => {
          if (!value) return "La raison est obligatoire !";
        }
      });

      if (raison) {
        try {
          const token = localStorage.getItem("access_token");
          // Remplacer par une suppression définitive
          await axios.delete(`/domiciliations/${record.id}`, {
            headers: { Authorization: `Bearer ${token}` },
            data: { raison } // Envoyer la raison dans le corps
          });

          // Supprimer de la liste locale immédiatemento
          this.domiciliations = this.domiciliations.filter(d => d.id !== record.id);

          this.showSuccessMessage("Domiciliation résiliée et supprimée définitivement");
        } catch (error) {
          console.error("Erreur lors de la suppression:", error);
          this.handleApiError(error);
        }
      }
    },



    // ==================== Utility Methods ====================
    generateReference() {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const milliseconds = String(now.getMilliseconds()).padStart(3, '0');

      return `DOM-${year}${month}${day}-${hours}${minutes}${seconds}${milliseconds}`;
    },

    getDaysRemaining(endDate) {
      if (!endDate) return 0;
      const today = new Date();
      const end = new Date(endDate);
      const diffTime = end - today;
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    },

    getTimeRemainingText(endDate) {
      const days = this.getDaysRemaining(endDate);

      if (days < 0) {
        return `Expiré (${Math.abs(days)} j)`;
      } else if (days === 0) {
        return "Expire aujourd'hui";
      } else if (days === 1) {
        return "1 jour restant";
      } else {
        return `${days} jours restants`;
      }
    },

    getTimeRemainingClass(endDate) {
      const days = this.getDaysRemaining(endDate);

      if (days < 0) {
        return "time-badge expired";
      } else if (days <= 5) {
        return "time-badge critical";
      } else if (days <= 15) {
        return "time-badge warning";
      } else {
        return "time-badge success";
      }
    },

    formatDate(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      return date.toLocaleDateString("fr-FR");
    },

    formatMontant(montant) {
      return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "MAD",
      }).format(montant);
    },

    getSituationClass(situation) {
      switch (situation) {
        case "DEMANDE":
          return "status-badge pending";
        case "EN_COURS":
          return "status-badge active";
        case "REJETTE":
          return "status-badge rejected";
        default:
          return "status-badge default";
      }
    },

    getSituationText(situation) {
      switch (situation) {
        case "DEMANDE":
          return "Demande";
        case "EN_COURS":
          return "En cours";
        case "REJETTE":
          return "Rejetée";
        default:
          return situation;
      }
    },

    formatDurationText(months) {
      switch (months) {
        case 3: return "3 mois";
        case 6: return "6 mois (demi-année)";
        case 12: return "1 an";
        case 15: return "1 an et 3 mois";
        case 18: return "1 an et demi";
        case 24: return "2 ans";
        default:
          if (months < 12) return `${months} mois`;
          else if (months === 12) return "1 an";
          else {
            const years = Math.floor(months / 12);
            const remainingMonths = months % 12;
            if (remainingMonths === 0) return `${years} an${years > 1 ? "s" : ""}`;
            else if (remainingMonths === 6) return `${years} an${years > 1 ? "s" : ""} et demi`;
            else return `${years} an${years > 1 ? "s" : ""} et ${remainingMonths} mois`;
          }
      }
    },

    // ==================== Filter & Pagination ====================
    clearFilters() {
      this.search = "";
      this.filters = {
        reference: "",
        client: "",
        situation: "",
        expiration: "",
      };
      this.currentPage = 1;
    },

    filterDomiciliations() {
      this.currentPage = 1;
    },

    onPageSizeChange() {
      this.currentPage = 1;
    },

    onPageChange() {
      // Page automatically updated via v-model
    },

    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    // ==================== Export Methods ====================
    exportToExcel() {
      const dataToExport = this.allFilteredData.map((dom) => ({
        Référence: dom.reference_numero || "-",
        Client: dom.client_nom,

        "Date de début": this.formatDate(dom.date_debut),
        "Date de fin": this.formatDate(dom.date_fin),
        "Temps restant": this.getTimeRemainingText(dom.date_fin),
        Montant: dom.montant,
        Situation: this.getSituationText(dom.situation),
        Paiement: dom.payement ? "Payé" : "Non payé",

      }));

      const worksheet = XLSX.utils.json_to_sheet(dataToExport);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "Domiciliations");

      const excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });
      const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      saveAs(data, `domiciliations_${new Date().toISOString().split("T")[0]}.xlsx`);
    },

    // Export PDF avec style d'impression professionnel
    exportToPDF() {
      if (!this.companyInfo) {
        this.generatePDFWithoutLogo();
        return;
      }

      // Créer une nouvelle fenêtre sans URL blob
      const printWindow = window.open('', '_blank');

      // Générer le contenu HTML
      const pdfContent = this.generatePrintableHTML();

      // Écrire le contenu directement dans la nouvelle fenêtre
      printWindow.document.open();
      printWindow.document.write(pdfContent);
      printWindow.document.close();

      // Configurer le titre et le focus
      printWindow.document.title = "Liste des domiciliations";
      printWindow.focus();
    },

    // Générer le HTML pour l'impression
    generatePrintableHTML() {
      const currentDate = new Date();
      const formattedDate = currentDate.toLocaleDateString('fr-FR');
      const formattedTime = currentDate.toLocaleTimeString('fr-FR');

      return `<!DOCTYPE html>
<html>
  <head>
    <title>Liste des domiciliations</title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
      @page {
        size: A4;
        margin: 0;
      }
    
      @media print {
        @page {
          size: A4;
          margin: 0;
        }
        body {
          margin: 0;
          padding: 15px;
        }
        html, body {
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }
        
        /* Masquer tous les en-têtes et pieds de page du navigateur */
        @page :left, @page :right, @page :first {
          margin: 0;
          size: A4;
          marks: none;
        }
        
        .no-print, .no-print * {
          display: none !important;
        }
        
        .content-wrapper {
          margin-bottom: 80px;
        }
      }
      
      body { 
        font-family: 'Roboto', Arial, sans-serif;
        margin: 0;
        padding: 20px;
        font-size: 12px;
        line-height: 1.4;
        color: #000;
        background: white;
      }
      
      .header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #ddd;
      }
      
      .company-logo {
        width: 80px;
        height: 80px;
        margin-right: 20px;
        border-radius: 8px;
        object-fit: contain;
        border: none !important;
      }
      
      .header-content {
        flex: 1;
      }
      
      .title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .date {
        font-size: 11px;
        color: #666;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      table { 
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 10px;
        background: white;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      th, td { 
        border: 1px solid #ddd;
        padding: 6px 4px;
        text-align: left;
        vertical-align: top;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      th { 
        background-color: #f8f9fa;
        font-weight: bold;
        font-size: 9px;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .signature-footer {
        position: fixed;
        bottom: 20px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #000;
        line-height: 1.2;
        font-family: 'Roboto', Arial, sans-serif;
      }

      .company-info {
        margin-top: 10px;
        font-size: 8px;
        line-height: 1.3;
        color: #000;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .no-print {
        margin-top: 30px;
        text-align: center;
      }
      
      .print-btn, .close-btn {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 12px 24px;
        margin: 0 10px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .close-btn {
        background-color: #6c757d;
      }
      
      .print-btn:hover {
        background-color: #0056b3;
      }
      
      .close-btn:hover {
        background-color: #545b62;
      }
      
      .badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
        color: #333;
        background-color: #f8f9fa;
        border: none;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .text-center {
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="content-wrapper">
      <div class="header">
        ${this.companyInfo?.logo ?
          `<img src="${this.getImageUrl(this.companyInfo.logo)}" alt="Logo ${this.companyInfo.nom}" class="company-logo" onerror="this.style.display='none'" />` :
          '<div class="company-logo" style="display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666; background: #f8f9fa; font-family: \'Roboto\', Arial, sans-serif;">LOGO</div>'
        }
        <div class="header-content">
          <div class="title">Liste des Domiciliations</div>
          <div class="date">Généré le ${formattedDate} à ${formattedTime}</div>
        </div>
      </div>
      
      <table>
        <thead>
          <tr>
            <th style="width: 15%;">Référence</th>
            <th style="width: 20%;">Client</th>
            <th style="width: 15%;">Gestionnaire</th>
            <th style="width: 15%;">Période</th>
            <th style="width: 10%;">Montant</th>
            <th style="width: 10%;">Situation</th>
            <th style="width: 10%;">Paiement</th>
            <th style="width: 5%;">Temps restant</th>
          </tr>
        </thead>
        <tbody>
          ${this.allFilteredData
          .map(
            (dom) => `
            <tr>
              <td>${dom.reference_numero || '-'}</td>
              <td>${dom.client_nom || 'N/A'}</td>
              <td>${dom.gestionnaire_nom || 'N/A'}</td>
              <td>${this.formatDate(dom.date_debut)} - ${this.formatDate(dom.date_fin)}</td>
              <td>${this.formatMontant(dom.montant)}</td>
              <td>${this.getSituationText(dom.situation)}</td>
              <td class="text-center">
                <span class="badge">
                  ${dom.payement ? 'Payé' : 'Non payé'}
                </span>
              </td>
              <td>${this.getTimeRemainingText(dom.date_fin)}</td>
            </tr>
          `
          )
          .join("")}
        </tbody>
      </table>
    </div>
    
    <div class="signature-footer">
      <div class="company-info">
        <div>
          ${this.companyInfo?.adresse || 'N/A'} | 
          Tél: ${this.companyInfo?.telephone || 'N/A'} | 
          Email: ${this.companyInfo?.email || 'N/A'}
        </div>
        <div>
          RC: ${this.companyInfo?.rc || 'N/A'} | 
          IF: ${this.companyInfo?.identifiant_fiscal || 'N/A'} | 
          TP: ${this.companyInfo?.tp || 'N/A'} | 
          ICE: ${this.companyInfo?.ice || 'N/A'}
        </div>
      </div>
    </div>
    
    <div class="no-print">
      <button onclick="window.print(); setTimeout(() => { window.close(); }, 1000);" class="print-btn">
        📄 Imprimer PDF
      </button>
      <button onclick="window.close()" class="close-btn">
        ✖ Fermer
      </button>
    </div>
    
    <script>
      window.onload = function() {
        document.title = "Liste des domiciliations";
      }
    <\/script>
  </body>
</html>`;
    },

    // Méthode de secours si les infos de société ne sont pas disponibles
    generatePDFWithoutLogo() {
      const doc = new jsPDF();
      doc.autoTable = autoTable.bind(null, doc);

      try {
        doc.setFont("Roboto", "normal");
      } catch (e) {
        doc.setFont("helvetica", "normal");
      }

      doc.setFontSize(18);
      doc.text("Liste des domiciliations", 14, 20);
      doc.setFontSize(11);
      doc.text(`Exporté le: ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);

      doc.autoTable({
        head: [["Référence", "Client", "Gestionnaire", "Période", "Montant", "Situation", "Paiement"]],
        body: this.allFilteredData.map((dom) => [
          dom.reference_numero || '-',
          dom.client_nom,
          dom.gestionnaire_nom,
          `${this.formatDate(dom.date_debut)} - ${this.formatDate(dom.date_fin)}`,
          this.formatMontant(dom.montant),
          this.getSituationText(dom.situation),
          dom.payement ? 'Payé' : 'Non payé'
        ]),
        startY: 40,
        styles: { fontSize: 9, font: "Roboto" },
        headStyles: {
          fillColor: [171, 194, 112], // #ABC270
          textColor: 255,
          fontSize: 10,
          fontStyle: "bold",
          font: "Roboto"
        },
        alternateRowStyles: { fillColor: [245, 245, 245] },
        margin: { top: 40, bottom: 30 },
      });

      if (this.companyInfo) {
        const finalY = doc.lastAutoTable.finalY || 40;
        doc.setFontSize(8);
        doc.text(`${this.companyInfo.nom || 'Société'}`, 14, finalY + 20);
        doc.text(`Adresse: ${this.companyInfo.adresse || 'N/A'} | Tél: ${this.companyInfo.telephone || 'N/A'}`, 14, finalY + 25);
        doc.text(`RC: ${this.companyInfo.rc || 'N/A'}  IF: ${this.companyInfo.identifiant_fiscal || 'N/A'}  TP: ${this.companyInfo.tp || 'N/A'}  ICE: ${this.companyInfo.ice || 'N/A'}`, 14, finalY + 30);
      }

      doc.save(`domiciliations_${new Date().toISOString().split("T")[0]}.pdf`);
    },

    // Fetch company information
    async fetchCompanyInfo() {
      try {
        const response = await axios.get('/societes');

        if (response.data) {
          this.companyInfo = {
            nom: response.data.nom_francais,
            adresse: response.data.adresse_siege_social_francais,
            telephone: response.data.telephone,
            email: response.data.email,
            site_web: response.data.website || response.data.site_web || response.data.url,
            logo: response.data.logo || response.data.logo_path,
            rc: response.data.rc,
            identifiant_fiscal: response.data.identifiant_fiscal,
            tp: response.data.tp,
            ice: response.data.ice
          };

          console.log("Informations société traitées:", this.companyInfo);
        } else {
          console.error("La réponse de l'API ne contient pas de données");
          this.companyInfo = null;
        }
      } catch (err) {
        console.error("Erreur lors du chargement des informations de la société:", err);
        this.companyInfo = null;
      }
    },

    // Get image URL for Laravel Storage
    getImageUrl(path) {
      if (!path) return null;
      return `http://localhost:8000/storage/${path}`;
    },

    // ==================== PDF Download ====================
    async downloadPDF(record) {
      try {
        this.downloadingId = record.id;
        this.isSubmitting = true;

        const apiUrl = `${process.env.VUE_APP_API_URL || ''}/domiciliations/${record.id}/pdf`;
        const token = localStorage.getItem("access_token");

        const response = await fetch(apiUrl, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });

        if (!response.ok) {
          throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `domiciliation_${record.id}.pdf`);

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        this.showSuccessMessage("Document PDF téléchargé avec succès");
      } catch (error) {
        console.error("Erreur lors du téléchargement du PDF:", error);
        this.showErrorMessage("Erreur lors du téléchargement du PDF: " + (error.message || "Erreur inconnue"));
      } finally {
        this.isSubmitting = false;
        this.downloadingId = null;
      }
    },

    async downloadGeneratedPDF(domiciliationId) {
      try {
        await this.downloadPDF({ id: domiciliationId });
      } catch (error) {
        console.error("Erreur lors du téléchargement du PDF après création:", error);
      }
    },

    // ==================== Data Fetching ====================
    async fetchDomiciliations() {
      try {
        const response = await axios.get("/domiciliations", {
          headers: this.getApiHeaders(),
        });
        const allDomiciliations = response.data.data || response.data;
        const domiciliations = allDomiciliations.filter(dom => dom.situation !== 'RESILIE');

        this.domiciliations = await Promise.all(
          domiciliations.map(async (dom) => {
            const client = this.clients.find((c) => c.id === dom.client_id) || {};
            const gestionnaire = this.gestionnaires.find((g) => g.id === dom.gestionnaire_id) || {};
            const duration = this.calculateDurationFromDates(dom.date_debut, dom.date_fin);

            return {
              ...dom,
              client_nom: `${client.nom_francais || ""}`.trim(),
              gestionnaire_nom: `${gestionnaire.nom || ""} ${gestionnaire.prenom || ""}`.trim(),
              duree_mois: duration.mois,
              duree_text: duration.text,
            };
          })
        );
      } catch (error) {
        console.error("Erreur lors du chargement des domiciliations:", error);
        this.showErrorMessage("Impossible de charger les domiciliations");
      }
    },

    async fetchClients() {
      try {
        const response = await axios.get("/clients", {
          headers: this.getApiHeaders(),
        });
        this.clients = response.data.data || response.data;
        console.log("Clients chargés:", this.clients);
      } catch (error) {
        console.error("Erreur lors du chargement des clients:", error);
        this.showErrorMessage("Impossible de charger les clients");
      }
    },

    async fetchGestionnaires() {
      try {
        const response = await axios.get("/gestionnaires", {
          headers: this.getApiHeaders(),
        });
        this.gestionnaires = response.data.gestionnaires || response.data;
        this.user.is_admin = response.data.role;
        console.log("Gestionnaires chargés:", this.gestionnaires);
        console.log("Role: ", response.data.role);
        console.log("User:", this.user);
        // Auto-sélectionner après le chargement
        this.autoSelectGestionnaire();
      } catch (error) {
        console.error("Erreur lors du chargement des gestionnaires:", error);
        this.showErrorMessage("Impossible de charger les gestionnaires");
      }
    },
    resetForm() {
      this.formData = {
        client_id: "",
        gestionnaire_id: "",
        date_debut: "",
        date_fin: "",
        duree_mois: "",
        montant: 0,
        situation: "DEMANDE",
        payement: false,
        reference_numero: this.generateReference(),
        notes: "",
      };
      this.editId = null;
    },
    // ==================== UI Helper Methods ====================
    redirectToAddClient() {
      this.closeModal("add-domiciliation");
      this.closeModal("edit-domiciliation");
      this.closeModal("renew-domiciliation");
      this.$router.push("/admin/addclient");
    },

    openAddGestionnaireModal() {
      const modalEl = document.getElementById('add-user');
      if (modalEl) {
        const modalInstance = Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      } else {
        console.error('Modal #add-user introuvable');
      }
    },

    onUserAdded(newUser) {
      this.fetchGestionnaires();
      // Mettre à jour le formulaire actif avec le nouveau gestionnaire
      if (this.addFormData.client_id) {
        this.addFormData.gestionnaire_id = newUser.id;
      }
      if (this.editFormData.client_id) {
        this.editFormData.gestionnaire_id = newUser.id;
      }
      if (this.renewFormData.client_id) {
        this.renewFormData.gestionnaire_id = newUser.id;
      }
    },

    // ==================== Error Handling ====================
    handleApiError(error) {
      if (error.response) {
        const errorMessage = error.response.data.error || error.response.data.details || "Erreur lors de l'opération";

        if (error.response.status === 401) {
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
        } else if (error.response.status === 422) {
          const validationErrors = error.response.data.errors;
          const errorMessages = Object.values(validationErrors).flat();
          this.showErrorMessage(errorMessages.join("\n"));
        } else {
          this.showErrorMessage(errorMessage);
        }
      } else {
        this.showErrorMessage("Erreur de connexion au serveur");
      }
    },

    // ==================== Success/Error Messages ====================
    showSuccessMessage(message) {
      Swal.fire({
        icon: "success",
        title: "Succès!",
        text: message,
        timer: 3000,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });
    },

    showErrorMessage(message) {
      Swal.fire({
        icon: "error",
        title: "Erreur!",
        text: message,
        confirmButtonText: "OK",
      });
    },
  },

  async mounted() {
    await this.fetchCompanyInfo();

    // Attends d’avoir récupéré le user d’abord
    if (!this.user) {
      console.warn("Aucun utilisateur connecté trouvé dans localStorage");
      return;
    }

    // Ensuite récupère les gestionnaires selon l’utilisateur
    await this.fetchGestionnaires();
    console.log("Gestionnaires chargés:", this.gestionnaires);

    // Une fois les gestionnaires bien reçus, applique l'auto sélection
    this.autoSelectGestionnaire();
    console.log("user:", this.user);
    // Ensuite tout le reste
    await this.fetchClients();
    await this.fetchDomiciliations();

    this.addFormData.reference_numero = this.generateReference();
  }
};
</script>

<style scoped>
/* ==================== Modern Header Styles ==================== */
.modern-header {
  background: #ABC270 !important;
  /* Couleur spécifique appliquée */
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

.stats-badges {
  display: flex;
  gap: 1rem;
}

.stat-badge {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  text-align: center;
  min-width: 80px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
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

.btn-add-new {
  background: linear-gradient(135deg, #FFFFFF33, #FFFFFF33);
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(238, 90, 36, 0.3);
  transition: all 0.3s ease;
}

.btn-add-new:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(238, 90, 36, 0.4);
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

/* ==================== Data Table Styles ==================== */
.data-table-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.table-container {
  background: white;
}

.modern-table {
  margin: 0;
}

.modern-table .ant-table-thead>tr>th {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
  padding: 1rem 0.75rem;
  font-size: 0.875rem;
}

.modern-table .ant-table-tbody>tr {
  transition: all 0.3s ease;
}

.modern-table .ant-table-tbody>tr:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.modern-table .ant-table-tbody>tr>td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

/* ==================== Table Cell Styles ==================== */
.reference-cell {
  display: flex;
  align-items: center;
}

.reference-badge {

  color: rgb(0, 0, 0);
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
}

.client-cell,
.gestionnaire-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

/* .client-avatar,
.gestionnaire-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
} */

.client-name {
  font-weight: 600;
  color: #495057;
}

.date-cell {
  display: flex;
  align-items: center;
  color: #6c757d;
  font-size: 0.875rem;
}

.time-remaining-cell {
  display: flex;
  align-items: center;
}

.time-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

/* .time-badge.expired {
  background-color: #343a40;
  color: white;
}

.time-badge.critical {
  background-color: #dc3545;
  color: white;
}

.time-badge.warning {
  background-color: #ffc107;
  color: #000;
}

.time-badge.success {
  background-color: #198754;
  color: white;
} */

.duration-cell {
  display: flex;
  align-items: center;
}

.duration-badge {

  color: rgb(0, 0, 0);
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.amount-cell {
  display: flex;
  align-items: center;
}

.amount-value {
  font-weight: 700;
  color: #28a745;
  font-size: 0.95rem;
}

.status-cell {
  display: flex;
  align-items: center;
}

.status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.status-badge.pending {
  background-color: #ffc107;
  color: #000;
}

.status-badge.active {
  background-color: #198754;
  color: white;
}

.status-badge.rejected {
  background-color: #dc3545;
  color: white;
}

.status-badge.default {
  background-color: #6c757d;
  color: white;
}

.payment-cell {
  display: flex;
  align-items: center;
}

.payment-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.payment-badge.paid {
  background-color: #198754;
  color: white;
}

.payment-badge.unpaid {
  background-color: #dc3545;
  color: white;
}

.action-cell {
  display: flex;
  align-items: center;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
  font-size: 0.875rem;
}

.edit-action {
  background: linear-gradient(135deg, #ABC270, #ABC270);
  color: white;
}

.edit-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.renew-action {
  background: linear-gradient(135deg, #ABC270, #ABC270);
  color: white;
}


.renew-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.download-action {
  background: linear-gradient(135deg, #ABC270, #ABC270);
  color: white;
}

.download-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.delete-action {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
}

.delete-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

/* ==================== Pagination Styles ==================== */
.pagination-wrapper {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  padding: 1.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
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

/* ==================== Modal Styles ==================== */
.modern-modal {
  border: none;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.modern-modal .modal-header {
  background: #ABC270;
  color: white;
}

.modal-title-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;

}

.contract-info-header {
  background: #e9ecef;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #dee2e6;
}

.contract-info-header h6 {
  margin: 0;
  font-weight: 600;
  color: #495057;
}


.contract-info-card {
  background: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #dee2e6;
  margin-bottom: 1.5rem;
  overflow: hidden;
}


.modal-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.modal-icon.edit-icon {
  background: rgba(0, 123, 255, 0.2);
}

.modal-icon.renew-icon {
  background: rgba(40, 167, 69, 0.2);
}

.modal-title-content h5 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.modal-subtitle {
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

.modern-modal .btn-close {
  background: none;
  /* Supprimer l'arrière-plan gris */
  border: none;
  color: white;
  /* Couleur du X */
  font-size: 1.5rem;
  /* Taille du X */
  line-height: 1;
  opacity: 1;
  text-shadow: none;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modern-modal .btn-close::before {
  content: 'X';
  /* Unicode for multiplication sign (X) */
  color: white;
  font-weight: bold;
}


.modern-modal .modal-body {
  padding: 2rem;
}

.modern-modal .modal-footer {
  background: #f8f9fa;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 2rem;
}

/* ==================== Form Styles ==================== */
.modern-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.section-title {
  color: #495057;
  font-weight: 600;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #dee2e6;
  display: flex;
  align-items: center;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
}

.form-label.required::after {
  content: " *";
  color: #35dc51;
}

.form-text {
  color: #6c757d;
  font-size: 0.8rem;
  margin-top: 0.25rem;
}

.payment-switch {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.switch-text {
  font-weight: 600;
  color: #495057;
}

/* ==================== Contract Info Alert ==================== */
.contract-info {
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #d1ecf1, #bee5eb);
  border-left: 4px solid #17a2b8;
  padding: 1.5rem;
}

.alert-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.alert-header h6 {
  color: #0c5460;
  font-weight: 700;
}

.contract-details {
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.detail-label {
  font-weight: 600;
  color: #0c5460;
}

.detail-value {
  color: #0c5460;
}

.alert-note {
  color: #0c5460;
  font-style: italic;
  opacity: 0.8;
}

/* ==================== Responsive Design ==================== */
@media (max-width: 1200px) {
  .filter-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }

  .stats-badges {
    flex-wrap: wrap;
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
    background-color: #ABC270;
    flex-direction: column;
    gap: 1rem;
  }

  .title-wrapper {
    flex-direction: column;
    text-align: center;
  }

  .stats-badges {
    justify-content: center;
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

  .modern-modal .modal-body {
    padding: 1rem;
  }

  .modern-modal .modal-footer {
    padding: 1rem;
  }

  .form-section {
    padding: 1rem;
  }

  .action-buttons {
    flex-wrap: wrap;
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

  .stat-badge {
    min-width: 70px;
    padding: 0.5rem 0.75rem;
  }

  .stat-number {
    font-size: 1.25rem;
  }

  .export-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
  }

  .btn-add-new {
    padding: 0.625rem 1.25rem;
  }
}
</style>