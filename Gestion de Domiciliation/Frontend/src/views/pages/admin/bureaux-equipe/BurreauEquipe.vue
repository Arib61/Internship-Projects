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
                <h4 class="page-title">Gestion des Bureaux</h4>
                <p class="page-subtitle">Gérez vos contrats en toute simplicité</p>
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
              <button class="action-btn export-btn" @click="printTable" data-bs-toggle="tooltip" title="Imprimer">
                <i class="fas fa-print"></i>

              </button>
            </div>


            <button class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#add-domiciliation">
              <i class="fas fa-plus-circle me-2"></i>
              <span>Nouveau Bureaux</span>
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
                        <button class="action-btn download-action"
                          @click.prevent="() => { downloadingId = record.id; downloadContract(record) }"
                          :disabled="isDownloading && downloadingId === record.id"
                          title="Télécharger le contrat (PDF ou Word)">
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
              <h5 class="modal-title">Ajouter un Bureau</h5>
              <p class="modal-subtitle">Créer un nouveau contrat de bureau</p>
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
                      <select v-model="addFormData.gestionnaire_id" class="form-select" required>
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
              <h5 class="modal-title">Modifier le Bureau</h5>
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
              <h5 class="modal-title">Renouveler le Bureau</h5>
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
import { Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell, Footer, AlignmentType, WidthType, HeadingLevel, ImageRun, Header } from "docx";

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
  name: 'BurreauEquipeManager',
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
        situation: "DEMANDE",
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
        situation: "DEMANDE",
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
    // ==================== API Configuration ====================
    getApiHeaders() {
      const token = localStorage.getItem("access_token");
      return {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
        Accept: "application/json",
      };
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
        const token = localStorage.getItem("access_token");

        if (!token) {
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
          return;
        }

        // Générer une référence si absente
        if (!this.addFormData.reference_numero) {
          this.addFormData.reference_numero = this.generateReference();
        }

        const payload = {
          ...this.addFormData,
          created_by: user.id,
          is_deleted: false,
          renewal_count: 0,
        };

        const response = await axios.post("/bureaux-equipe", payload, {
          headers: this.getApiHeaders(),
        });

        if (response.data && response.data.data) {
          const record = response.data.data; // l'objet complet du bureau

          this.showSuccessMessage("Bureau créé avec succès");
          this.resetAddForm();
          this.closeModal("add-domiciliation");
          await this.fetchDomiciliations();

          // ✅ Appelle la méthode d'affichage de choix (PDF ou Word)
          await this.downloadContract(record);
        }
      } catch (error) {
        console.error("Erreur lors de la création du bureau:", error);
        this.handleApiError(error);
      } finally {
        this.isSubmitting = false;
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

        const response = await axios.put(`/bureaux-equipe/${this.editId}`, payload, {
          headers: this.getApiHeaders(),
        });

        if (response.data && response.data.data) {
          this.showSuccessMessage("Bureau mise à jour avec succès");
          this.resetEditForm();
          this.closeModal("edit-domiciliation");
          await this.fetchDomiciliations();
        }
      } catch (error) {
        console.error("Erreur lors de la mise à jour du burreau:", error);
        this.handleApiError(error);
      } finally {
        this.isSubmitting = false;
      }
    },

    // RENEW - Renouveler une domiciliation (créer nouveau + archiver ancien)
    async renewDomiciliation() {
      try {
        this.isSubmitting = true;
        const token = localStorage.getItem("access_token");

        if (!token) {
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
          return;
        }

        // Utiliser directement la route de renouvellement
        const response = await axios.post(
          `/bureaux-equipe/${this.renewFormData.previous_id}/renew`,
          {}, // Pas besoin de payload supplémentaire
          {
            headers: this.getApiHeaders()
          }
        );

        if (response.data) {
          this.showSuccessMessage("Bureau renouvelé avec succès !");
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

          // Utilisez la route de résiliation au lieu de suppression
          await axios.post(`/bureaux-equipe/${record.id}/resiliate`, {
            raison
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });

          // Supprimer de la liste locale
          this.domiciliations = this.domiciliations.filter(d => d.id !== record.id);

          this.showSuccessMessage("Bureau résilié avec succès");
        } catch (error) {
          console.error("Erreur lors de la résiliation:", error);

          if (error.response) {
            console.error("Code HTTP:", error.response.status);
            console.error("Données de l'erreur:", error.response.data);

            const details = error.response.data.details || error.response.data.error || error.message;

            this.showErrorMessage(`Erreur ${error.response.status}: ${details}`);
          } else if (error.request) {
            // La requête a été faite mais aucune réponse
            console.error("Aucune réponse du serveur:", error.request);
            this.showErrorMessage("Le serveur ne répond pas. Veuillez réessayer plus tard.");
          } else {
            // Autre erreur
            console.error("Erreur inconnue:", error.message);
            this.showErrorMessage("Erreur inattendue: " + error.message);
          }
        }

      }
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
        async imageToBase64(imageUrl) {
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
    },

    async getImageUrl(imagePath) {
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
    },
    async downloadContract(record) {
      if (this.isDownloading) return;

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
        await this.downloadPDFContract(record);
      } else if (result.isDenied) {
        await this.downloadWordContract(record);
      }
    },

    async downloadPDFContract(record) {
      this.isDownloading = true;
      try {
        const response = await axios.get(`/bureaux-equipe/${record.id}/contract-data`);
        const { client, societe, gerant } = response.data.data;

        await this.generatePDFContract(record, client, societe, gerant);

        Swal.fire({
          icon: "success",
          title: "PDF téléchargé",
          text: "Le contrat PDF a été téléchargé avec succès.",
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });
      } catch (error) {
        console.error("Erreur PDF :", error);
        this.showErrorMessage("Erreur lors du téléchargement du contrat PDF.");
      } finally {
        this.isDownloading = false;
      }
    },


    async downloadWordContract(record) {
      this.isDownloading = true;
      try {
        const response = await axios.get(`/bureaux-equipe/${record.id}/contract-data`);
        const { client, societe, gerant } = response.data.data;

        await this.generateWordContract(record, client, societe, gerant);

        Swal.fire({
          icon: "success",
          title: "Word téléchargé",
          text: "Le contrat Word a été téléchargé avec succès.",
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });
      } catch (error) {
        console.error("Erreur Word :", error);
        this.showErrorMessage("Erreur lors du téléchargement du contrat Word.");
      } finally {
        this.isDownloading = false;
      }
    },

    async generatePDFContract(bureau, client, societe, gerant) {
      await this.loadRobotoFont();
      console.log('Génération du contrat PDF pour le bureau:', societe);
      const doc = new jsPDF();

      // Ajouter la police Roboto (fallback vers Helvetica si non disponible)
      try {
        doc.setFont("Roboto", "normal");
      } catch (e) {
        doc.setFont("helvetica", "normal");
      }

      let yPosition = 20;
      const pageHeight = doc.internal.pageSize.height;
      const pageWidth = doc.internal.pageSize.width;
      const margin = 10;
      const lineHeight = 6;
      const logoHeight = 30; // Hauteur fixe pour le logo
      const footerHeight = 20; // Hauteur réservée pour le footer
      const contentStartY = margin + logoHeight + 10; // Position de départ du contenu
      const contentEndY = pageHeight - footerHeight - 10; // Position de fin du contenu

      // Variable pour stocker le logo une fois chargé
      let logoData = null;
      let logoImageType = 'PNG'; // Type par défaut

      // Fonction pour ajouter le logo en haut à gauche
      const addLogoToPage = () => {
        if (logoData && logoData.dataURL) {
          try {
            // Calculer les dimensions pour le logo en haut à gauche
            const maxLogoWidth = 40;
            const maxLogoHeight = logoHeight;

            let logoWidth = maxLogoWidth;
            let logoHeightCalc = (logoData.scaledHeight * maxLogoWidth) / logoData.scaledWidth;

            if (logoHeightCalc > maxLogoHeight) {
              logoHeightCalc = maxLogoHeight;
              logoWidth = (logoData.scaledWidth * maxLogoHeight) / logoData.scaledHeight;
            }

            // Position en haut à gauche
            doc.addImage(logoData.dataURL, 'PNG', margin, margin, logoWidth, logoHeightCalc);
            console.log(`Logo ajouté en haut à gauche - Dimensions: ${logoWidth}x${logoHeightCalc}`);
          } catch (error) {
            console.warn('Erreur lors de l\'ajout du logo:', error);
          }
        }
      };

      // Fonction pour ajouter le footer en bas de page
      const addFooterToPage = () => {
        const footerY = pageHeight - footerHeight;

        doc.setFontSize(9);
        try {
          doc.setFont("Roboto", "normal");
        } catch (e) {
          doc.setFont("helvetica", "normal");
        }

        const footerContent = [
          `TEL: ${societe?.telephone || 'N/A'} SIEGE SOCIAL: ${societe?.adresse_siege_social_francais || 'N/A'}`,
          `RC: ${societe?.rc || 'N/A'} - IF: ${societe?.identifiant_fiscal || 'N/A'} - TP: ${societe?.tp || 'N/A'} - ICE: ${this.companyInfo?.ice || 'N/A'}`
        ];

        // Centrer le texte horizontalement
        const centerX = pageWidth / 2;

        footerContent.forEach((line, index) => {
          doc.text(line, centerX, footerY + (index * 5), { align: 'center' });
        });
      };

      // Fonction améliorée pour convertir l'image en base64 avec redimensionnement
      const imageToBase64 = (url) => {
        return new Promise((resolve, reject) => {
          const img = new Image();
          img.crossOrigin = 'anonymous'; // Pour éviter les problèmes CORS

          img.onload = function () {
            try {
              const canvas = document.createElement('canvas');
              const ctx = canvas.getContext('2d');

              // Calculer les dimensions pour une qualité optimale
              const maxWidth = 400; // Largeur maximale pour une bonne qualité
              const maxHeight = 300; // Hauteur maximale

              let { width, height } = this;

              // Maintenir le ratio d'aspect
              if (width > height) {
                if (width > maxWidth) {
                  height = (height * maxWidth) / width;
                  width = maxWidth;
                }
              } else {
                if (height > maxHeight) {
                  width = (width * maxHeight) / height;
                  height = maxHeight;
                }
              }

              // Utiliser une résolution plus élevée pour éviter la pixellisation
              const scale = 2; // Facteur d'échelle pour une meilleure qualité
              canvas.width = width * scale;
              canvas.height = height * scale;

              // Configurer le contexte pour une meilleure qualité
              ctx.imageSmoothingEnabled = true;
              ctx.imageSmoothingQuality = 'high';
              ctx.scale(scale, scale);

              // Dessiner l'image sur le canvas avec antialiasing
              ctx.drawImage(this, 0, 0, width, height);

              // Convertir en base64 avec une qualité maximale
              const dataURL = canvas.toDataURL('image/png', 1.0);
              resolve({
                dataURL,
                originalWidth: this.naturalWidth,
                originalHeight: this.naturalHeight,
                scaledWidth: width,
                scaledHeight: height
              });
            } catch (error) {
              console.error('Erreur lors de la conversion canvas:', error);
              reject(error);
            }
          };

          img.onerror = function (error) {
            console.error('Erreur lors du chargement de l\'image:', error);
            reject(error);
          };

          img.src = url;
        });
      };

      // Fonction pour détecter le type d'image à partir de l'URL
      const getImageTypeFromUrl = (url) => {
        // Vérifier si url est une string, sinon utiliser PNG par défaut
        if (typeof url !== 'string') {
          console.warn('URL n\'est pas une string, utilisation de PNG par défaut');
          return 'PNG';
        }

        const extension = url.toLowerCase().split('.').pop().split('?')[0]; // Enlever les paramètres de requête
        const typeMap = {
          'jpg': 'JPEG',
          'jpeg': 'JPEG',
          'png': 'PNG',
          'gif': 'PNG', // Convertir GIF en PNG pour jsPDF
          'webp': 'PNG', // Convertir WebP en PNG pour jsPDF
          'bmp': 'PNG', // Convertir BMP en PNG pour jsPDF
          'svg': 'PNG'  // Convertir SVG en PNG pour jsPDF
        };
        return typeMap[extension] || 'PNG';
      };

      // Ajouter le logo de la société en en-tête
      if (societe.logo) {
        try {
          console.log('Tentative de chargement du logo:', societe.logo);
          const logoUrl = await this.getImageUrl(societe.logo); // Attendre la résolution de la promesse
          console.log('URL du logo traitée:', logoUrl);

          // Détecter le type d'image
          logoImageType = getImageTypeFromUrl(logoUrl);
          console.log('Type d\'image détecté:', logoImageType);

          logoData = await imageToBase64(logoUrl);

          if (logoData && logoData.dataURL) {
            console.log('Logo converti en base64 avec succès');
            console.log('Dimensions originales:', logoData.originalWidth, 'x', logoData.originalHeight);
            console.log('Dimensions redimensionnées:', logoData.scaledWidth, 'x', logoData.scaledHeight);
          } else {
            console.warn('Logo base64 est vide');
          }
        } catch (error) {
          console.error('Erreur lors du traitement du logo:', error);
        }
      } else {
        console.log('Aucun logo fourni');
      }

      // Ajouter le logo et footer à la première page
      addLogoToPage();
      addFooterToPage();

      // Positionner le contenu après le logo
      yPosition = contentStartY;

      // En-tête avec informations de la société
      doc.setFontSize(10);
      try {
        doc.setFont("Roboto", "normal");
      } catch (e) {
        doc.setFont("helvetica", "normal");
      }

      const companyName = societe?.societe_nom || this.companyInfo?.nom || '-';
      const companyAddress = societe?.adresse_siege_social_francais || this.companyInfo?.adresse || '44, AVENUE DE FRANCE, 3ÈME ÉTAGE, APPT N° 16, AGDAL-RABAT';

      yPosition += 25;

      // Fonction pour vérifier le saut de page avec logo
      const checkPageBreak = (neededSpace = 10) => {
        if (yPosition + neededSpace > pageHeight - margin) {
          doc.addPage();
          yPosition = margin;

          // Réajouter le logo sur la nouvelle page si disponible
          if (logoData && logoData.dataURL) {
            try {
              // Calculer des dimensions plus petites pour les pages suivantes
              const headerMaxWidth = 35;
              const headerMaxHeight = 25;

              let headerWidth = headerMaxWidth;
              let headerHeight = (logoData.scaledHeight * headerMaxWidth) / logoData.scaledWidth;

              if (headerHeight > headerMaxHeight) {
                headerHeight = headerMaxHeight;
                headerWidth = (logoData.scaledWidth * headerMaxHeight) / logoData.scaledHeight;
              }

              doc.addImage(logoData.dataURL, 'PNG', margin, yPosition, headerWidth, headerHeight);
              yPosition += headerHeight + 5;
            } catch (error) {
              console.warn('Erreur lors de l\'ajout du logo sur nouvelle page:', error);
              yPosition += 15;
            }
          } else {
            yPosition += 15;
          }
        }
      };

      // Fonction pour ajouter du texte avec police Roboto
      const addText = (text, fontSize = 12, style = "normal", align = "left") => {
        doc.setFontSize(fontSize);
        try {
          doc.setFont("Roboto", style);
        } catch (e) {
          doc.setFont("helvetica", style);
        }

        const splitText = doc.splitTextToSize(text, 170);
        checkPageBreak(splitText.length * lineHeight);

        if (align === "center") {
          doc.text(splitText, 105, yPosition, { align: "center" });
        } else {
          doc.text(splitText, margin, yPosition);
        }

        yPosition += splitText.length * lineHeight + 5;
      };

      console.log('Worked up to here', bureau.renewal_count > 0 ? 'Renouvellement' : 'Nouveau contrat');

      // Titre du contrat
      addText(`${bureau.renewal_count > 0 ? 'RENOUVELLEMENT DU' : ''} CONTRAT DE LOCATION DE BUREAU ÉQUIPÉ`, 18, "bold", "center");
      yPosition += 10;

      // Ligne de séparation
      doc.setLineWidth(0.5);
      doc.line(margin, yPosition, 190, yPosition);
      yPosition += 15;

      addText("ENTRE LES SOUSSIGNÉS", 14, "bold", "center");
      yPosition += 5;

      // Contenu du contrat avec police Roboto
      const prestataire = `La société « ${companyName} » ${societe?.form_juridique || '${}'}, au capital de ${societe?.capital_social || '100.000,00'} DHS, inscrite au registre de commerce sous le numéro ${societe?.rc || this.companyInfo?.rc || '172295'}, ayant son siège social à ${companyAddress}, représentée par le gérant ${societe?.nom_complet_francais || 'Mr. MOURAD EL MANSOURI'}.

Ci-après dénommée le prestataire`;

      addText(prestataire, 12, "normal");

      const beneficiaire = `La société ${client?.nom_francais || '-'}, ICE N° ${client?.ice || '-'}, représentée par ${gerant?.nom || client?.nom_francais || '-'}, CIN N° ${gerant?.cin || '-'}.

Ci-après dénommée le BÉNÉFICIAIRE`;

      addText(beneficiaire, 12, "normal");

      addText("IL A ÉTÉ CONVENU ET ARRÊTÉ CE QUI SUIT :", 12, "bold", "center");

      // Articles du contrat complets
      const articles = [
        {
          title: "ARTICLE 1 : DÉFINITION DES PRESTATIONS",
          content: `La société « ${companyName} » ${societe?.form_juridique || '${}'} s'engage à fournir au bénéficiaire les prestations suivantes à savoir :
• BUREAU ÉQUIPÉ N° 8 situé au ${companyAddress}.
• ACCÈS À LA SALLE DE BUREAU DE RÉUNION (selon disponibilité et sur réservation 48h à l'avance)
• GESTION DE COURRIER
• RÉCEPTION DES APPELS
• RÉCEPTION DES CLIENTS

Les services sont fournis en fonction des horaires propres à la société « ${companyName} » ${societe?.form_juridique || '${}'} à savoir :
- Du lundi au Vendredi de 9H à 17H, samedi de 9H à 13H à l'exception des jours fériés.`
        },
        {
          title: "ARTICLE 2 : DURÉE",
          content: `Le contrat est prévu pour une durée de ${Math.floor((new Date(bureau.date_fin) - new Date(bureau.date_debut)) / (1000 * 60 * 60 * 24 * 30.44) / 12) > 0
            ? `${Math.floor((new Date(bureau.date_fin) - new Date(bureau.date_debut)) / (1000 * 60 * 60 * 24 * 30.44) / 12)} an${Math.floor((new Date(bureau.date_fin) - new Date(bureau.date_debut)) / (1000 * 60 * 60 * 24 * 30.44) / 12) > 1 ? 's' : ''}`
            : `${Math.round((new Date(bureau.date_fin) - new Date(bureau.date_debut)) / (1000 * 60 * 60 * 24 * 30.44))} mois`} renouvelable par tacite reconduction.
Le loyer sera majoré de dix 10% tous les 3 ans sur la base du loyer précédent.
Le présent contrat prend effet du : ${this.formatDate(bureau.date_debut)} Au ${this.formatDate(bureau.date_fin)}.`
        },
        {
          title: "ARTICLE 3 : PRIX",
          content: `Le présent contrat est conclu moyennant le paiement par le bénéficiaire, une redevance de ${this.formatMontantText(bureau.montant)} dirhams soit (${bureau.montant.toFixed(2)} T.T.C) par mois.
Le gérant unique ${gerant?.nom || client?.nom_francais || '-'}, se porte personnellement solidaire pour le paiement de la redevance objet du présent contrat.
Le paiement de la redevance locative se fait du 01 Au 05 de chaque mois par un virement.
Pour tout retard de paiement une pénalité de 10% sera appliquée après le 10ème du mois.`
        },
        {
          title: "ARTICLE 4 : DOCUMENTS À FOURNIR",
          content: `Pour permettre l'établissement du présent contrat, le bénéficiaire doit fournir :
• Photocopie de la CIN ou passeport du gérant ;
• Adresse et téléphone du gérant ;
• Photocopie de certificat négatif relatif à la dénomination ;
• Photocopie du dossier juridique et fiscal de la société ;
• Copie du certificat de patente justifiant de l'établissement du siège social ou d'entreprise dans les locaux de la société ${client?.nom_francais || '-'} ;
• Copie de registre de commerce justifiant de l'établissement du siège social ou d'entreprise dans les locaux de la société ${client?.nom_francais || '-'}.`
        },
        {
          title: "ARTICLE 5 : OBLIGATIONS",
          content: `Le bénéficiaire devra informer la société « ${companyName} » ${societe?.form_juridique || '${}'} de tous les changements suivants :
• Changement du gérant ;
• Changement d'activité ;
• Changement de la forme juridique de la société ;
• Modification de l'adresse et de téléphone du gérant ;

Le bénéficiaire devra justifier auprès de la société « ${companyName} » ${societe?.form_juridique || '${}'} du règlement de sa patente.`
        },
        {
          title: "ARTICLE 6 : CESSATION DU CONTRAT",
          content: `A/ CESSATION DU FAIT DU BÉNÉFICIAIRE
Le bénéficiaire peut mettre fin à ce contrat à tout moment tout en respectant le délai de préavis de deux mois qui devra être notifié à la société ${companyName} ${societe?.form_juridique || '${}'} par lettre recommandée avec accusé de réception.

Avant l'expiration de la durée du préavis, le bénéficiaire devra justifier auprès de la société « ${companyName} » ${societe?.form_juridique || '${}'} soit de son transfert de siège soit de la dissolution de sa société ou de son entreprise par la remise d'un extrait de registre de commerce notificatif.

B/ CESSATION DU FAIT DU PRESTATAIRE
La société « ${companyName} » ${societe?.form_juridique || '${}'} pourra procéder à la résiliation du présent contrat quinze jours après une mise en demeure adressée au gérant de la société bénéficiaire par lettre recommandée avec accusé de réception dans les cas suivants :
• Défaut de règlement de la redevance les quinze jours qui suivent son échéance ;
• Non-respect des clauses et conditions du présent contrat ;
• Non-respect du règlement intérieur du centre ;
• Défaut de règlement avéré des impôts et taxes dus par le bénéficiaire ;
• Défaut de règlement des cotisations sociales ;
• Poursuites pénales liées à l'activité professionnelle du bénéficiaire.

À l'expiration du délai de quinze jours sus-indiqué et faute par le bénéficiaire d'avoir régularisé sa situation, le tribunal de commerce sera saisi par la société « ${companyName} » ${societe?.form_juridique || '${}'} en vue de faire constater judiciairement les manquements. Ces informations seront communiquées aux services fiscaux et à la CNSS.

N.B : La caution ne peut être restituée au client qu'après justification du transfert du siège de son entreprise et présentation de la nouvelle patente et du registre de commerce Modèle J.`
        },
        {
          title: "ARTICLE 7 : ATTRIBUTION DE JURIDICTION",
          content: "Tous litiges qui pourraient surgir entre les parties seront jugés par les tribunaux de Rabat."
        },
        {
          title: "ARTICLE 8 : NULLITÉ D'UNE DISPOSITION",
          content: "Au cas où tout ou partie des clauses contenues dans le présent contrat se trouveraient être ou devenir en contradiction avec une disposition légale d'ordre public, la disposition nulle ne sera pas appliquée, mais le surplus de la convention continuera à produire son plein et entier effet."
        },
        {
          title: "ARTICLE 9 : FRAIS",
          content: "Les frais d'établissement, ainsi que de timbre, d'enregistrement et autres, auxquels le présent contrat donnera lieu seront supportés par le bénéficiaire."
        },
        {
          title: "ARTICLE 10 : CLAUSE DE CAUTIONNEMENT SOLIDAIRE ET PERSONNEL",
          content: `Le gérant de la société ${client?.nom_francais || '-'}, représenté par ${gerant?.nom || client?.nom_francais || '-'}, CIN N° ${gerant?.cin || '-'}, se porte caution personnelle et solidaire et garantit le paiement de toutes les sommes qui seraient dues, par la société ${client?.nom_francais || '-'} au titre de ses engagements auprès des personnes physiques ou morales, organismes financiers, direction des impôts, et tribunal de commerce en assumant ainsi la responsabilité financière, vis-à-vis de toute procédure de nantissement ou de liquidation.`
        }
      ];

      // Ajouter tous les articles avec police Roboto
      articles.forEach(article => {
        checkPageBreak(30);
        addText(article.title, 12, "bold");
        addText(article.content, 11, "normal");
        yPosition += 5;
      });

      // Signatures
      checkPageBreak(60);
      yPosition += 20;

      doc.setFontSize(12);
      try {
        doc.setFont("Roboto", "bold");
      } catch (e) {
        doc.setFont("helvetica", "bold");
      }

      const leftX = 30;
      const rightX = 120;

      doc.text("SIGNATURE :", leftX, yPosition);
      doc.text("SIGNATURE :", rightX, yPosition);
      yPosition += 15;

      doc.text(`${companyName}`, leftX, yPosition);
      doc.text(`${client?.nom_francais || '-'}`, rightX, yPosition);
      yPosition += 10;

      try {
        doc.setFont("Roboto", "italic");
      } catch (e) {
        doc.setFont("helvetica", "italic");
      }
      doc.text("Représentée par", leftX, yPosition);
      doc.text("Représentée par", rightX, yPosition);
      yPosition += 10;

      try {
        doc.setFont("Roboto", "bold");
      } catch (e) {
        doc.setFont("helvetica", "bold");
      }
      doc.text(`${societe?.nom_complet_francais || 'Mr. MOURAD EL MANSOURI'}`, leftX, yPosition);
      doc.text(`${gerant?.nom || client?.nom_francais || '-'}`, rightX, yPosition);

      // ============ AJOUT DU FOOTER EN BAS DE LA DERNIÈRE PAGE ============

      // 1. Vérifier s'il reste assez de place pour le footer
      const finalFooterHeight = 15; // Renamed to avoid conflict
      const pageBottom = doc.internal.pageSize.height - margin;

      // Si pas assez de place, créer une nouvelle page
      if (yPosition + finalFooterHeight > pageBottom) {
        doc.addPage();
        yPosition = margin;
      }

      // 2. Positionner le footer en bas de la dernière page
      const footerY = pageBottom - finalFooterHeight;

      // 3. Ajouter le contenu du footer
      doc.setFontSize(9);
      try {
        doc.setFont("Roboto", "normal");
      } catch (e) {
        doc.setFont("helvetica", "normal");
      }

      const footerContent = [
        `TEL: ${societe?.telephone || 'N/A'} SIEGE SOCIAL: ${societe?.adresse_siege_social_francais || 'N/A'}`,
        `RC: ${societe?.rc || 'N/A'} - IF: ${societe?.identifiant_fiscal || 'N/A'} - TP: ${societe?.tp || 'N/A'} - ICE: ${this.companyInfo?.ice || 'N/A'}`
      ];

      // Centrer le texte horizontalement
      const centerX = doc.internal.pageSize.getWidth() / 2;

      footerContent.forEach((line, index) => {
        doc.text(line, centerX, footerY + (index * 5), { align: 'center' });
      });

      // Télécharger le PDF
      doc.save(`Contrat_Bureau_${bureau.reference_numero}.pdf`);
      console.log('PDF généré avec succès');
    },

    // Méthode complète pour générer le contrat Word avec logo
    async generateWordContract(bureau, client, societe, gerant) {
      console.log('Génération du contrat Word pour le bureau:', bureau);
      const professionalFont = "Calibri";
      const fontColor = "000000";

      const companyName = societe?.societe_nom || this.companyInfo?.nom || '-';
      const companyAddress = societe?.adresse_siege_social_francais || this.companyInfo?.adresse || '44, AVENUE DE FRANCE, 3ÈME ÉTAGE, APPT N° 16, AGDAL-RABAT';
      const footer = `${companyName}, Adresse : ${companyAddress} | Tél: ${this.companyInfo?.telephone || 'N/A'} | Email: ${this.companyInfo?.email || 'N/A'} | RC: ${this.companyInfo?.rc || 'N/A'} | ICE: ${this.companyInfo?.ice || 'N/A'}`;

      // ============ CHARGEMENT DU LOGO IDENTIQUE AU PDF ============

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

      const getDuration = () => {
        const start = new Date(bureau.date_debut);
        const end = new Date(bureau.date_fin);
        const diff = end - start;
        const months = Math.round(diff / (1000 * 60 * 60 * 24 * 30.44));
        const years = Math.floor(months / 12);
        const remainingMonths = months % 12;

        return years > 0
          ? `${years} an${years > 1 ? 's' : ''}${remainingMonths ? ` et ${remainingMonths} mois` : ''}`
          : `${months} mois`;
      };

      // Helper for article titles
      const articleTitle = (num, title) =>
        new Paragraph({
          children: [
            new TextRun({
              text: `ARTICLE ${num} : ${title.toUpperCase()}`,
              font: professionalFont,
              bold: true,
              color: fontColor,
              size: 26,
            }),
          ],
          alignment: AlignmentType.RIGHT, // Changed to LEFT
          spacing: { after: 100 },
        });

      // Helper for article content with sub-content formatting
      const articleContent = (content) => {
        const lines = content.split('\n');
        return lines.map(line => {
          if (line.trim().startsWith('-') || line.trim().startsWith('•')) {
            return new Paragraph({
              children: [
                new TextRun({
                  text: line.trim(),
                  font: professionalFont,
                  color: fontColor,
                  size: 22,
                }),
              ],
              alignment: AlignmentType.RIGHT, // Added LEFT alignment
              indent: {
                left: 720,  // 0.5 inch (1.27 cm)
                right: 720, // right "margin"
              },
              spacing: {
                before: 200, // ← Top spacing (approx 0.14 inch or ~0.36 cm)
                after: 50,   // Bottom spacing
              },
            });
          } else if (line.trim().length === 0) {
            return new Paragraph({ text: "" });
          } else {
            return new Paragraph({
              children: [
                new TextRun({
                  text: line,
                  font: professionalFont,
                  color: fontColor,
                  size: 24,
                }),
              ],
              alignment: AlignmentType.RIGHT, // Added LEFT alignment
              spacing: { after: 100 },
            });
          }
        });
      };

      // Articles with improved formatting for sub-content
      const articles = [
        [
          articleTitle(1, "Définition des prestations"),
          ...articleContent(
            `La société ${companyName} s'engage à fournir au bénéficiaire les prestations suivantes à savoir :
- Bureau équipé N° 8 situé au ${companyAddress}
- Accès à la salle de réunion (selon disponibilité et sur réservation 48h à l'avance) 
- Gestion du courrier
- Réception des appels
- Réception des clients

Les services sont fournis en fonction des horaires propres à la société « ${companyName} » à savoir : 
- Du lundi au vendredi de 9H à 17H, samedi de 9H à 13H à l'exception des jours fériés.`
          ),
        ],
        [
          articleTitle(2, "Durée"),
          ...articleContent(
            `Le contrat est prévu pour une durée de ${getDuration()} renouvelable par tacite reconduction.
Le loyer sera majoré de dix 10% tous les 3 ans sur la base du loyer précédent. 
Le présent contrat prend effet du : ${this.formatDate(bureau.date_debut)} au ${this.formatDate(bureau.date_fin)}.`
          ),
        ],
        [
          articleTitle(3, "Prix"),
          ...articleContent(
            `Le présent contrat est conclu moyennant le paiement par le bénéficiaire, une redevance de ${this.formatMontantText(bureau.montant)} Dirhams soit (${bureau.montant.toFixed(2)} T.T.C) par mois.
Le gérant unique ${gerant?.nom || '-'} se porte personnellement solidaire pour le paiement de la redevance objet du présent contrat.
Le paiement de la redevance locative se fait du 01 au 05 de chaque mois par virement.
Pour tout retard de paiement une pénalité de 10% sera appliquée après le 10ème du mois.`
          ),
        ],
        [
          articleTitle(4, "Documents à fournir"),
          ...articleContent(
            `Pour permettre l'établissement du présent contrat, le bénéficiaire doit fournir :
• Photocopie de la CIN ou passeport du gérant 
• Adresse et téléphone du gérant
• Photocopie de certificat négatif relatif à la dénomination 
• Photocopie du dossier juridique et fiscal de la société
• Copie du certificat de patente justifiant de l'établissement du siège social ou d'entreprise dans les locaux de la société ${client?.nom_francais || '-'}
• Copie de registre de commerce justifiant de l'établissement du siège social ou d'entreprise dans les locaux de la société ${client?.nom_francais || '-'}`
          ),
        ],
        [
          articleTitle(5, "Obligations"),
          ...articleContent(
            `Le bénéficiaire devra informer la société « ${companyName} » ${societe?.form_juridique || 'SARL'} de tous les changements suivants :

• Changement du gérant
• Changement d'activité
• Changement de la forme juridique de la société
• Modification de l'adresse et de téléphone du gérant
         
Le bénéficiaire devra justifier auprès de la société « ${companyName} » ${societe?.form_juridique || 'SARL'} du règlement de sa patente.`
          ),
        ],
        [
          articleTitle(6, "Cessation du contrat"),
          ...articleContent(
            `A/ CESSATION DU FAIT DU BÉNÉFICIAIRE 

Le bénéficiaire peut mettre fin à ce contrat à tout moment tout en respectant le délai de préavis de deux mois qui devra être notifié à la société ${companyName} ${societe?.form_juridique || 'SARL'} par lettre recommandée avec accusé de réception.

Avant l'expiration de la durée du préavis, le bénéficiaire devra justifier auprès de la société « ${companyName} » ${societe?.form_juridique || 'SARL'} soit de son transfert de siège soit de la dissolution de sa société ou de son entreprise par la remise d'un extrait de registre de commerce notificatif.

B/ CESSATION DU FAIT DU PRESTATAIRE

La société « ${companyName} » ${societe?.form_juridique || 'SARL'} pourra procéder à la résiliation du présent contrat quinze jours après une mise en demeure adressée au gérant de la société bénéficiaire par lettre recommandée avec accusé de réception dans les cas suivants :
• Défaut de règlement de la redevance les quinze jours qui suivent son échéance
• Non-respect des clauses et conditions du présent contrat
• Non-respect du règlement intérieur du centre
• Défaut de règlement avéré des impôts et taxes dus par le bénéficiaire
• Défaut de règlement des cotisations sociales
• Poursuites pénales liées à l'activité professionnelle du bénéficiaire

À l'expiration du délai de quinze jours sus-indiqué et faute par le bénéficiaire d'avoir régularisé sa situation, le tribunal de commerce sera saisi par la société « ${companyName} » ${societe?.form_juridique || 'SARL'} en vue de faire constater judiciairement les manquements. Ces informations seront communiquées aux services fiscaux et à la CNSS.

N.B : La caution ne peut être restituée au client qu'après justification du transfert du siège de son entreprise et présentation de la nouvelle patente et du registre de commerce Modèle J.`
          ),
        ],
        [
          articleTitle(7, "Attribution de juridiction"),
          ...articleContent(`Tous litiges qui pourraient surgir entre les parties seront jugés par les tribunaux de Rabat.`),
        ],
        [
          articleTitle(8, "Nullité d'une disposition"),
          ...articleContent(`Au cas où tout ou partie des clauses contenues dans le présent contrat se trouveraient être ou devenir en contradiction avec une disposition légale d'ordre public, la disposition nulle ne sera pas appliquée, mais le surplus de la convention continuera à produire son plein et entier effet.`),
        ],
        [
          articleTitle(9, "Frais"),
          ...articleContent(`Les frais d'établissement, ainsi que de timbre, d'enregistrement et autres, auxquels le présent contrat donnera lieu seront supportés par le bénéficiaire.`),
        ],
        [
          articleTitle(10, "Clause de cautionnement solidaire et personnel"),
          ...articleContent(
            `Le gérant de la société ${client?.nom_francais || '-'}, représenté par ${gerant?.nom || '-'}, CIN N° ${gerant?.cin || '-'}, se porte caution personnelle et solidaire et garantit le paiement de toutes les sommes qui seraient dues, par la société ${client?.nom_francais || '-'} au titre de ses engagements auprès des personnes physiques ou morales, organismes financiers, direction des impôts, et tribunal de commerce en assumant ainsi la responsabilité financière, vis-à-vis de toute procédure de nantissement ou de liquidation.`
          ),
        ],
      ];

      // Signatures table (side by side)
      const signaturesTable = new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        rows: [
          new TableRow({
            children: [
              new TableCell({
                width: { size: 50, type: WidthType.PERCENTAGE },
                children: [
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: `${companyName}`,
                        font: professionalFont,
                        bold: true,
                        color: fontColor,
                        size: 24
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: "Représentée par",
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: societe?.president_nom || societe?.nom_complet_francais || 'Mr. MOURAD EL MANSOURI',
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: "_________________________",
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                    spacing: { before: 400, after: 100 },
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: "Signature",
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                ],
              }),
              new TableCell({
                width: { size: 50, type: WidthType.PERCENTAGE },
                children: [
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: `${client?.nom_francais || 'BENZAL CAR'}`,
                        font: professionalFont,
                        bold: true,
                        color: fontColor,
                        size: 24
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: "Représentée par",
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: gerant?.nom || client?.president_nom || client?.nom_complet_francais || 'Mr. BENZAL OUTMAN',
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: "_________________________",
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                    spacing: { before: 400, after: 100 },
                  }),
                  new Paragraph({
                    children: [
                      new TextRun({
                        text: "Signature",
                        font: professionalFont,
                        color: fontColor,
                        size: 22
                      }),
                    ],
                    alignment: AlignmentType.CENTER,
                  }),
                ],
              }),
            ],
          }),
        ],
      });

      // Document definition
      const docChildren = [];

      // ============ AJOUTER LE LOGO EN PREMIER ============

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

      const headerChildren = []

      if (logoBase64) {
        try {
          headerChildren.push(
            new Paragraph({
              children: [
                new ImageRun({
                  data: logoBase64,
                  transformation: {
                    width: 150,
                    height: 56,
                  }
                }),
              ],
              alignment: AlignmentType.RIGHT,
              spacing: {
                after: 400  // Add space after logo (increase this value for more space)
              },
            })
          );
          console.log("Logo added to header successfully")
        } catch (error) {
          console.error("Error adding logo to document header:", error)
        }
      }

      // ============ TITRE ET EN-TÊTE ============
      docChildren.push(
        new Paragraph({
          children: [
            new TextRun({
              text: `${bureau.renewal_count > 0 ? 'RENOUVELLEMENT DU ' : ''} CONTRAT DE LOCATION DE BUREAU ÉQUIPÉ`,
              font: professionalFont,
              bold: true,
              color: fontColor,
              size: 32,
            }),
          ],
          heading: HeadingLevel.TITLE,
          alignment: AlignmentType.CENTER, // KEEP CENTERED
          spacing: { after: 300 },
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: "ENTRE LES SOUSSIGNÉS",
              font: professionalFont,
              bold: true,
              color: fontColor,
              size: 26,
            }),
          ],
          alignment: AlignmentType.CENTER, // KEEP CENTERED
          italics: true,
          spacing: { after: 200 },
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: `La société « ${companyName} » ${societe?.form_juridique || 'SARL'}, au capital de ${societe?.capital_social || '100.000,00'} DHS, RC ${societe?.rc || this.companyInfo?.rc || '172295'}, sise à ${companyAddress}, représentée par ${societe?.nom_complet_francais || 'Mr. MOURAD EL MANSOURI'}.`,
              font: professionalFont,
              color: fontColor,
              size: 24,
            }),
          ],
          alignment: AlignmentType.RIGHT,
          spacing: { after: 100 },
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: "Ci-après dénommée le PRESTATAIRE",
              font: professionalFont,
              bold: true,
              color: fontColor,
              size: 24,
            }),
          ],
          alignment: AlignmentType.RIGHT, // Fixed - was AlignmentType which was incorrect
          spacing: { after: 200 },
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: `La société ${client?.nom_francais || '-'}, ICE N° ${client?.ice || '-'}, représentée par ${gerant?.nom || '-'}, CIN N° ${gerant?.cin || '-'}`,
              font: professionalFont,
              color: fontColor,
              size: 24,
            }),
          ],
          alignment: AlignmentType.RIGHT, // Added alignment
          spacing: { after: 100 },
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: "Ci-après dénommée le BÉNÉFICIAIRE",
              font: professionalFont,
              bold: true,
              color: fontColor,
              size: 24,
            }),
          ],
          alignment: AlignmentType.RIGHT, // KEEP RIGHT as requested
          spacing: { after: 200 },
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: "IL A ÉTÉ CONVENU ET ARRÊTÉ CE QUI SUIT :",
              font: professionalFont,
              bold: true,
              color: fontColor,
              size: 26,
            }),
          ],
          alignment: AlignmentType.CENTER, // KEEP CENTERED
          spacing: { after: 300 },
        })
      );

      // ============ AJOUTER TOUS LES ARTICLES ============
      articles.forEach(article => {
        docChildren.push(...article); // Removed the invalid AlignmentType.RIGHT parameter
      });

      // ============ ESPACEMENT AVANT SIGNATURES ============
      docChildren.push(
        new Paragraph({ text: "", spacing: { after: 400 } }),
        signaturesTable
      );

      // ============ FOOTER SEULEMENT EN BAS DE LA PAGE ============
      // Espacement pour pousser le footer vers le bas
      docChildren.push(
        new Paragraph({
          text: "",
          spacing: { before: 800 }
        })
      );

      // ============ CRÉATION DU DOCUMENT ============
      const doc = new Document({
        sections: [
          {
            properties: {
              page: {
                margin: {
                  top: 1440,
                  right: 1440,
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
            children: docChildren,
          },
        ],
      });

      // ============ GÉNÉRATION ET TÉLÉCHARGEMENT ============
      const blob = await Packer.toBlob(doc);
      saveAs(blob, `Contrat_Bureau_${bureau.reference_numero}.docx`);
      console.log('Document Word généré avec succès');
    },




    formatMontantText(montant) {
      const nombres = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'
      ];

      const dizaines = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'
      ];

      if (montant < 20) {
        return nombres[montant];
      } else if (montant < 100) {
        const diz = Math.floor(montant / 10);
        const unit = montant % 10;
        return dizaines[diz] + (unit > 0 ? '-' + nombres[unit] : '');
      } else if (montant < 1000) {
        const cent = Math.floor(montant / 100);
        const reste = montant % 100;
        let result = (cent === 1 ? 'cent' : nombres[cent] + ' cent');
        if (reste > 0) {
          result += ' ' + this.formatMontantText(reste);
        }
        return result;
      } else if (montant < 1000000) {
        const mille = Math.floor(montant / 1000);
        const reste = montant % 1000;
        let result = (mille === 1 ? 'mille' : this.formatMontantText(mille) + ' mille');
        if (reste > 0) {
          result += ' ' + this.formatMontantText(reste);
        }
        return result;
      }

      return montant.toString();
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
        Gestionnaire: dom.gestionnaire_nom,
        "Date de début": this.formatDate(dom.date_debut),
        "Date de fin": this.formatDate(dom.date_fin),
        "Temps restant": this.getTimeRemainingText(dom.date_fin),
        Montant: dom.montant,
        Situation: this.getSituationText(dom.situation),
        Paiement: dom.payement ? "Payé" : "Non payé",
        Notes: dom.notes || "-",
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

    exportToPDF() {
      const doc = new jsPDF();
      doc.autoTable = autoTable.bind(null, doc);
      doc.setFontSize(18);
      doc.text("Liste des domiciliations", 14, 20);
      doc.setFontSize(11);
      doc.text(`Exporté le: ${new Date().toLocaleDateString()}`, 14, 30);
      doc.autoTable({
        head: [["Référence", "Client", "Gestionnaire", "Date début", "Date fin", "Temps restant", "Montant", "Situation", "Paiement"]],
        body: this.allFilteredData.map((dom) => [
          dom.reference_numero || "-",
          dom.client_nom,
          dom.gestionnaire_nom,
          this.formatDate(dom.date_debut),
          this.formatDate(dom.date_fin),
          this.getTimeRemainingText(dom.date_fin),
          this.formatMontant(dom.montant),
          this.getSituationText(dom.situation),
          dom.payement ? "Payé" : "Non payé",
        ]),
        startY: 40,
        styles: { fontSize: 10, font: "helvetica" },
        headStyles: {
          fillColor: [41, 128, 185],
          textColor: 255,
          fontSize: 11,
          fontStyle: "bold",
        },
        alternateRowStyles: { fillColor: [245, 245, 245] },
        margin: { top: 40 },
      });
      doc.save(`domiciliations_${new Date().toISOString().split("T")[0]}.pdf`);
    },

    printTable() {
      const printWindow = window.open("", "_blank");
      const tableHTML = `
        <!DOCTYPE html>
        <html>
          <head>
            <title>Liste des domiciliations</title>
            <meta charset="UTF-8">
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              table { width: 100%; border-collapse: collapse; margin-top: 20px; }
              th, td { border: 1px solid #ddd; padding: 12px 8px; text-align: left; }
              th { background-color: #f5f5f5; font-weight: bold; }
              .header { margin-bottom: 30px; }
              .date { color: #666; margin: 10px 0 20px; }
              @media print { .no-print { display: none; } body { margin: 0; padding: 15px; } }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Liste des domiciliations</h1>
              <div class="date">Date d'impression: ${new Date().toLocaleDateString()}</div>
            </div>
            <table>
              <thead>
                <tr>
                  <th>Référence</th><th>Client</th><th>Gestionnaire</th><th>Date début</th><th>Date fin</th>
                  <th>Temps restant</th><th>Montant</th><th>Situation</th><th>Paiement</th>
                </tr>
              </thead>
              <tbody>
                ${this.allFilteredData.map(dom => `
                  <tr>
                    <td>${dom.reference_numero || "-"}</td><td>${dom.client_nom}</td><td>${dom.gestionnaire_nom}</td>
                    <td>${this.formatDate(dom.date_debut)}</td><td>${this.formatDate(dom.date_fin)}</td>
                    <td>${this.getTimeRemainingText(dom.date_fin)}</td><td>${this.formatMontant(dom.montant)}</td>
                    <td>${this.getSituationText(dom.situation)}</td><td>${dom.payement ? "Payé" : "Non payé"}</td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
            <div class="no-print" style="margin-top: 30px;">
              <button onclick="window.print()" style="padding: 10px 20px; margin-right: 10px;">Imprimer</button>
              <button onclick="window.close()" style="padding: 10px 20px;">Fermer</button>
            </div>
          </body>
        </html>
      `;
      printWindow.document.write(tableHTML);
      printWindow.document.close();
    },

    // ==================== PDF Download ====================
    async downloadPDF(record) {
      try {
        this.downloadingId = record.id;
        this.isSubmitting = true;

        const apiUrl = `${process.env.VUE_APP_API_URL || ''}/bureaux-equipe/${record.id}/pdf`;
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
        const response = await axios.get("/bureaux-equipe", {
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
        this.gestionnaires = response.data.gestionnaires || [];
        this.user.is_admin = response.data.role;
      } catch (error) {
        console.error("Erreur lors du chargement des gestionnaires:", error);
      }
    },

    // ==================== UI Helper Methods ====================
    redirectToAddClient() {
      this.closeModal("add-domiciliation");
      this.closeModal("edit-domiciliation");
      this.closeModal("renew-domiciliation");
      this.$router.push("/admin/addclient");
    },

    openAddGestionnaireModal() {
      this.$refs.usersModal.showModal();
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
    await Promise.all([this.fetchClients(), this.fetchGestionnaires()]);
    await this.fetchDomiciliations();
    // Générer les références pour les nouveaux formulaires
    this.addFormData.reference_numero = this.generateReference();
  },
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