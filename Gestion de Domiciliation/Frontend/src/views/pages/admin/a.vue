<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <div class="page-header">
        <div class="add-item d-flex">
          <div class="page-title">
            <h4>Gestion des Domiciliations</h4>
            <h6>Gérez vos contrats de domiciliation</h6>
          </div>
        </div>
        <ul class="table-top-head">
          <li>
            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Exporter en PDF"
              @click="exportToPDF">
              <img src="@/assets/img/icons/pdf.svg" alt="PDF" />
            </a>
          </li>
          <li>
            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Exporter en Excel"
              @click="exportToExcel">
              <img src="@/assets/img/icons/excel.svg" alt="Excel" />
            </a>
          </li>
          <li>
            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Imprimer"
              @click="printTable">
              <VueFeather type="printer" />
            </a>
          </li>
        </ul>
        <div class="page-btn">
          <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal"
            data-bs-target="#add-domiciliation">
            <VueFeather type="plus-circle" class="me-2" />
            Ajouter Nouvelle Domiciliation
          </a>
        </div>
      </div>

      <div class="card table-list-card">
        <div class="card-body">
          <!-- Advanced Search and Filters -->
          <div class="table-top">
            <div class="search-set">
              <div class="row g-3 align-items-end">
                <div class="col-md-3">
                  <label class="form-label">Recherche générale</label>
                  <div class="search-input">
                    <input type="text" class="form-control" placeholder="Rechercher..." v-model="search"
                      @input="filterDomiciliations" />
                    <a href="" class="btn btn-searchset">
                      <VueFeather type="search" />
                    </a>
                  </div>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Référence</label>
                  <input type="text" class="form-control" placeholder="Référence..." v-model="filters.reference"
                    @input="filterDomiciliations" />
                </div>
                <div class="col-md-2">
                  <label class="form-label">Client</label>
                  <select class="form-select" v-model="filters.client" @change="filterDomiciliations">
                    <option value="">Tous les clients</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.nom_francais }}
                    </option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Situation</label>
                  <select class="form-select" v-model="filters.situation" @change="filterDomiciliations">
                    <option value="">Toutes</option>
                    <option value="DEMANDE">Demande</option>
                    <option value="EN_COURS">En cours</option>
                    <option value="REJETTE">Rejetée</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Statut expiration</label>
                  <select class="form-select" v-model="filters.expiration" @change="filterDomiciliations">
                    <option value="">Tous</option>
                    <option value="expired">Expirés</option>
                    <option value="danger">Critique (≤5 jours)</option>
                    <option value="warning">Attention (≤15 jours)</option>
                    <option value="success">Valides (>15 jours)</option>
                  </select>
                </div>
                <div class="col-md-1">
                  <button class="btn btn-outline-secondary" @click="clearFilters" title="Effacer les filtres">
                    <VueFeather type="x" size="16" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Table Section -->
          <div class="table-responsive">
            <a-table class="table datanew" :columns="columns" :data-source="filteredData" :row-selection="{}"
              :pagination="false">
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'reference'">
                  <div class="text-nowrap">
                    <span class="fw-bold text-primary">{{ record.reference_numero || "-" }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'client'">
                  <div class="text-nowrap">
                    <span class="fw-semibold">{{ record.client_nom }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'gestionnaire'">
                  <div class="text-nowrap">{{ record.gestionnaire_nom }}</div>
                </template>
                <template v-else-if="column.key === 'date_debut'">
                  <div class="text-nowrap">{{ formatDate(record.date_debut) }}</div>
                </template>
                <template v-else-if="column.key === 'date_fin'">
                  <div class="text-nowrap">{{ formatDate(record.date_fin) }}</div>
                </template>
                <template v-else-if="column.key === 'time_remaining'">
                  <div class="text-nowrap">
                    <span :class="getTimeRemainingClass(record.date_fin)">
                      {{ getTimeRemainingText(record.date_fin) }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'duree'">
                  <div class="text-nowrap">
                    <span class="badge bg-info">{{ record.duree_text }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'montant'">
                  <div class="text-nowrap">{{ formatMontant(record.montant) }}</div>
                </template>
                <template v-else-if="column.key === 'situation'">
                  <div class="text-nowrap">
                    <span :class="getSituationClass(record.situation)">
                      {{ getSituationText(record.situation) }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'payement'">
                  <div class="text-nowrap">
                    <span :class="record.payement ? 'badge bg-success' : 'badge bg-danger'">
                      {{ record.payement ? "Payé" : "Non payé" }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'action'">
                  <td class="action-table-data">
                    <div class="edit-delete-action">
                      <a class="me-2 p-2 mb-0 action-btn edit-btn" data-bs-toggle="modal"
                        data-bs-target="#edit-domiciliation" title="Modifier"
                        @click.prevent="openEditModal(record)">
                        <i data-feather="edit" class="feather-edit"></i>
                      </a>
                      <a class="me-2 p-2 mb-0 action-btn renew-btn" data-bs-toggle="modal"
                        data-bs-target="#renew-domiciliation" title="Renouveler" 
                        @click.prevent="openRenewModal(record)" :disabled="isSubmitting">
                        <i data-feather="refresh-cw" class="feather-refresh-cw"></i>
                        <span v-if="isSubmitting && renewingId === record.id"
                          class="spinner-border spinner-border-sm ms-1" role="status"></span>
                      </a>
                      <a class="me-2 p-2 mb-0 action-btn download-btn" href="javascript:void(0);"
                        data-bs-toggle="tooltip" title="Télécharger PDF" @click.prevent="downloadPDF(record)"
                        :disabled="isSubmitting">
                        <i data-feather="download" class="feather-download"></i>
                        <span v-if="isSubmitting && downloadingId === record.id"
                          class="spinner-border spinner-border-sm ms-1" role="status"></span>
                      </a>
                      <a class="me-2 confirm-text p-2 mb-0 action-btn delete-btn" href="javascript:void(0);"
                        data-bs-toggle="tooltip" title="Supprimer" @click.prevent="onDeleteDomiciliation(record)">
                        <i data-feather="trash-2" class="feather-trash-2"></i>
                      </a>
                    </div>
                  </td>
                </template>
              </template>
            </a-table>
          </div>

          <!-- Pagination -->
          <div class="custom-pagination-wrapper d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <div class="d-flex align-items-center">
              <label class="me-2 fw-semibold">Afficher :</label>
              <select v-model="pageSize" class="form-select form-select-sm" style="width: auto; min-width: 80px"
                @change="onPageSizeChange">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span class="ms-2 text-muted">entrées par page</span>
            </div>

            <div class="pagination-info text-muted">
              Affichage de {{ startRecord }} à {{ endRecord }} sur {{ allFilteredData.length }} entrées
            </div>

            <div class="pagination-controls d-flex align-items-center gap-3">
              <div class="pagination-nav d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === 1"
                  @click="goToPage(1)" title="Première page">
                  <VueFeather type="chevrons-left" size="14" />
                </button>
                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === 1"
                  @click="goToPage(currentPage - 1)" title="Page précédente">
                  <VueFeather type="chevron-left" size="14" />
                </button>

                <div class="page-selector d-flex align-items-center gap-2">
                  <span class="text-muted small">Page :</span>
                  <select v-model="currentPage" class="form-select form-select-sm page-select" @change="onPageChange">
                    <option v-for="page in totalPages" :key="page" :value="page">{{ page }}</option>
                  </select>
                  <span class="text-muted small">sur {{ totalPages }}</span>
                </div>

                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === totalPages"
                  @click="goToPage(currentPage + 1)" title="Page suivante">
                  <VueFeather type="chevron-right" size="14" />
                </button>
                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === totalPages"
                  @click="goToPage(totalPages)" title="Dernière page">
                  <VueFeather type="chevrons-right" size="14" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Ajouter Domiciliation -->
  <div class="modal fade" id="add-domiciliation" tabindex="-1" aria-labelledby="add-domiciliation-label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="add-domiciliation-label">Ajouter une Domiciliation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveDomiciliation">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Client</label>
                <div class="input-group">
                  <select v-model="addFormData.client_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un client</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.nom_francais }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="redirectToAddClient">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Gestionnaire</label>
                <div class="input-group">
                  <select v-model="addFormData.gestionnaire_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un gestionnaire</option>
                    <option v-for="gestionnaire in gestionnaires" :key="gestionnaire.id" :value="gestionnaire.id">
                      {{ gestionnaire.nom }} {{ gestionnaire.prenom }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="openAddGestionnaireModal">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de début</label>
                <input type="date" class="form-control" v-model="addFormData.date_debut" @change="calculateEndDateForAdd"
                  required />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Durée</label>
                <select class="form-select" v-model="addFormData.duree_mois" @change="calculateEndDateForAdd" required>
                  <option value="" disabled>Sélectionner une durée</option>
                  <option value="3">3 mois</option>
                  <option value="6">6 mois (demi-année)</option>
                  <option value="12">1 an</option>
                  <option value="15">1 an et 3 mois</option>
                  <option value="18">1 an et demi</option>
                  <option value="24">2 ans</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de fin</label>
                <input type="date" class="form-control" v-model="addFormData.date_fin" readonly />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Montant</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" v-model="addFormData.montant" required />
                  <span class="input-group-text">MAD</span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Situation</label>
                <select class="form-select" v-model="addFormData.situation" required>
                  <option value="DEMANDE">Demande</option>
                  <option value="EN_COURS">En cours</option>
                  <option value="REJETTE">Rejetée</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Paiement</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" v-model="addFormData.payement" id="payementSwitch" />
                  <label class="form-check-label" for="payementSwitch">
                    {{ addFormData.payement ? "Payé" : "Non payé" }}
                  </label>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Numéro de référence</label>
                <input type="text" class="form-control" v-model="addFormData.reference_numero" maxlength="50" readonly />
                <small class="text-muted">Généré automatiquement</small>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea class="form-control" v-model="addFormData.notes" rows="3"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                {{ isSubmitting ? 'Création en cours...' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Modifier Domiciliation -->
  <div class="modal fade" id="edit-domiciliation" tabindex="-1" aria-labelledby="edit-domiciliation-label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="edit-domiciliation-label">Modifier la Domiciliation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="updateDomiciliation">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Client</label>
                <div class="input-group">
                  <select v-model="editFormData.client_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un client</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.nom_francais }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="redirectToAddClient">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Gestionnaire</label>
                <div class="input-group">
                  <select v-model="editFormData.gestionnaire_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un gestionnaire</option>
                    <option v-for="gestionnaire in gestionnaires" :key="gestionnaire.id" :value="gestionnaire.id">
                      {{ gestionnaire.nom }} {{ gestionnaire.prenom }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="openAddGestionnaireModal">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de début</label>
                <input type="date" class="form-control" v-model="editFormData.date_debut" @change="calculateEndDateForEdit"
                  required />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Durée</label>
                <select class="form-select" v-model="editFormData.duree_mois" @change="calculateEndDateForEdit" required>
                  <option value="" disabled>Sélectionner une durée</option>
                  <option value="3">3 mois</option>
                  <option value="6">6 mois (demi-année)</option>
                  <option value="12">1 an</option>
                  <option value="15">1 an et 3 mois</option>
                  <option value="18">1 an et demi</option>
                  <option value="24">2 ans</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de fin</label>
                <input type="date" class="form-control" v-model="editFormData.date_fin" readonly />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Montant</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" v-model="editFormData.montant" required />
                  <span class="input-group-text">MAD</span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Situation</label>
                <select class="form-select" v-model="editFormData.situation" required>
                  <option value="DEMANDE">Demande</option>
                  <option value="EN_COURS">En cours</option>
                  <option value="REJETTE">Rejetée</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Paiement</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" v-model="editFormData.payement" id="editPayementSwitch" />
                  <label class="form-check-label" for="editPayementSwitch">
                    {{ editFormData.payement ? "Payé" : "Non payé" }}
                  </label>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Numéro de référence</label>
                <input type="text" class="form-control" v-model="editFormData.reference_numero" maxlength="50" readonly />
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea class="form-control" v-model="editFormData.notes" rows="3"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Mettre à jour
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Renouveler Domiciliation -->
  <div class="modal fade" id="renew-domiciliation" tabindex="-1" aria-labelledby="renew-domiciliation-label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="renew-domiciliation-label">Renouveler la Domiciliation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Informations de l'ancien contrat -->
          <div class="alert alert-info mb-4" v-if="originalDomiciliation">
            <h6><i class="fas fa-info-circle me-2"></i>Renouvellement de contrat</h6>
            <div class="row">
              <div class="col-md-6">
                <strong>Référence actuelle:</strong> {{ originalDomiciliation.reference_numero }}<br>
                <strong>Client:</strong> {{ originalDomiciliation.client_nom }}<br>
                <strong>Date fin actuelle:</strong> {{ formatDate(originalDomiciliation.date_fin) }}
              </div>
              <div class="col-md-6">
                <strong>Montant actuel:</strong> {{ formatMontant(originalDomiciliation.montant) }}<br>
                <strong>Durée actuelle:</strong> {{ originalDomiciliation.duree_text }}<br>
                <strong>Gestionnaire:</strong> {{ originalDomiciliation.gestionnaire_nom }}
              </div>
            </div>
            <small class="text-muted">L'ancien contrat sera automatiquement archivé lors du renouvellement.</small>
          </div>

          <form @submit.prevent="renewDomiciliation">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Client</label>
                <div class="input-group">
                  <select v-model="renewFormData.client_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un client</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.nom_francais }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="redirectToAddClient">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Gestionnaire</label>
                <div class="input-group">
                  <select v-model="renewFormData.gestionnaire_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un gestionnaire</option>
                    <option v-for="gestionnaire in gestionnaires" :key="gestionnaire.id" :value="gestionnaire.id">
                      {{ gestionnaire.nom }} {{ gestionnaire.prenom }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="openAddGestionnaireModal">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de début</label>
                <input type="date" class="form-control" v-model="renewFormData.date_debut" @change="calculateEndDateForRenew"
                  required />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Durée</label>
                <select class="form-select" v-model="renewFormData.duree_mois" @change="calculateEndDateForRenew" required>
                  <option value="" disabled>Sélectionner une durée</option>
                  <option value="3">3 mois</option>
                  <option value="6">6 mois (demi-année)</option>
                  <option value="12">1 an</option>
                  <option value="15">1 an et 3 mois</option>
                  <option value="18">1 an et demi</option>
                  <option value="24">2 ans</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de fin</label>
                <input type="date" class="form-control" v-model="renewFormData.date_fin" readonly />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Montant</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" v-model="renewFormData.montant" required />
                  <span class="input-group-text">MAD</span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Situation</label>
                <select class="form-select" v-model="renewFormData.situation" required>
                  <option value="DEMANDE">Demande</option>
                  <option value="EN_COURS">En cours</option>
                  <option value="REJETTE">Rejetée</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Paiement</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" v-model="renewFormData.payement" id="renewPayementSwitch" />
                  <label class="form-check-label" for="renewPayementSwitch">
                    {{ renewFormData.payement ? "Payé" : "Non payé" }}
                  </label>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Nouvelle référence</label>
                <input type="text" class="form-control" v-model="renewFormData.reference_numero" maxlength="50" readonly />
                <small class="text-muted">Nouvelle référence générée automatiquement</small>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea class="form-control" v-model="renewFormData.notes" rows="3"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                <i class="fas fa-sync-alt me-2"></i>
                {{ isSubmitting ? 'Renouvellement en cours...' : 'Renouveler' }}
              </button>
            </div>
          </form>
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
        date_debut: today,
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
      this.calculateEndDateForRenew();
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
          const responseData = response.data.data;

          if (responseData.pdf_path) {
            await this.downloadGeneratedPDF(responseData.domiciliation_id);
          }

          this.showSuccessMessage("Domiciliation créée avec succès");
          this.resetAddForm();
          this.closeModal("add-domiciliation");
          await this.fetchDomiciliations();
        }
      } catch (error) {
        console.error("Erreur lors de la création de la domiciliation:", error);
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
          await axios.put(`/domiciliations/${this.renewFormData.previous_id}/renew`, {
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
        this.handleApiError(error);
      } finally {
        this.isSubmitting = false;
      }
    },

    // DELETE
    async onDeleteDomiciliation(record) {
      const result = await Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        text: "Vous ne pourrez pas revenir en arrière !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui, supprimer !",
        cancelButtonText: "Annuler",
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/domiciliations/${record.id}`, {
            headers: this.getApiHeaders(),
          });

          this.showSuccessMessage("Domiciliation supprimée avec succès");
          await this.fetchDomiciliations();
        } catch (error) {
          console.error("Erreur lors de la suppression de la domiciliation:", error);
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
        return "badge bg-dark";
      } else if (days <= 5) {
        return "badge bg-danger";
      } else if (days <= 15) {
        return "badge bg-warning";
      } else {
        return "badge bg-success";
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
          return "badge bg-warning";
        case "EN_COURS":
          return "badge bg-success";
        case "REJETTE":
          return "badge bg-danger";
        default:
          return "badge bg-secondary";
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
        const domiciliations = response.data.data || response.data;

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
        this.gestionnaires = response.data.data || response.data;
      } catch (error) {
        console.error("Erreur lors du chargement des gestionnaires:", error);
        this.showErrorMessage("Impossible de charger les gestionnaires");
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
.custom-pagination-wrapper {
  background-color: #f8f9fa;
  border-radius: 6px;
  padding: 12px 16px;
}

.pagination-btn {
  min-width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-select {
  width: 70px;
  height: 36px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  text-align: center;
}

.page-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.pagination-info {
  font-size: 0.875rem;
  font-weight: 500;
}

.page-selector {
  background-color: white;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.edit-btn:hover {
  background-color: #007bff;
  color: white;
}

.renew-btn:hover {
  background-color: #28a745;
  color: white;
}

.download-btn:hover {
  background-color: #17a2b8;
  color: white;
}

.delete-btn:hover {
  background-color: #dc3545;
  color: white;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Time remaining badge styles */
.badge.bg-dark {
  background-color: #343a40 !important;
}

.badge.bg-danger {
  background-color: #dc3545 !important;
}

.badge.bg-warning {
  background-color: #ffc107 !important;
  color: #000 !important;
}

.badge.bg-success {
  background-color: #198754 !important;
}

/* Filter section styling */
.table-top .row {
  margin-bottom: 1rem;
}

.form-label {
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

/* Renewal modal specific styles */
#renew-domiciliation .alert-info {
  border-left: 4px solid #0dcaf0;
}

#renew-domiciliation .btn-success {
  background-color: #198754;
  border-color: #198754;
}

#renew-domiciliation .btn-success:hover {
  background-color: #157347;
  border-color: #146c43;
}

@media (max-width: 768px) {
  .custom-pagination-wrapper {
    flex-direction: column;
    gap: 12px;
    align-items: stretch !important;
  }

  .pagination-controls {
    justify-content: center;
  }

  .pagination-info {
    text-align: center;
  }

  .table-top .row .col-md-3,
  .table-top .row .col-md-2,
  .table-top .row .col-md-1 {
    margin-bottom: 1rem;
  }
}
</style>