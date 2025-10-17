<template>
  <!-- Ajouter Domiciliation -->
  <div class="modal fade" id="add-domiciliation">
    <div class="modal-dialog modal-dialog-centered custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 custom-modal-header">
              <div class="page-title">
                <h4>Créer une domiciliation</h4>
              </div>
              <button
                type="button"
                class="close"
                data-bs-dismiss="modal"
                aria-label="Fermer"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body custom-modal-body">
              <form @submit.prevent="submitForm">
                <div class="mb-3">
                  <label class="form-label">Domiciliation</label>
                  <input type="text" class="form-control" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Slug de domiciliation</label>
                  <input type="text" class="form-control" />
                </div>
                <div class="mb-0">
                  <div
                    class="status-toggle modal-status d-flex justify-content-between align-items-center"
                  >
                    <span class="status-label">Statut</span>
                    <input type="checkbox" id="domiciliation-status-add" class="check" checked="" />
                    <label for="domiciliation-status-add" class="checktoggle"></label>
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
                  <button type="submit" class="btn btn-submit">Créer</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Ajouter Domiciliation -->

  <!-- Modifier Domiciliation -->
  <div class="modal fade" id="edit-domiciliation">
    <div class="modal-dialog modal-dialog-centered custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 custom-modal-header">
              <div class="page-title">
                <h4>Modifier la domiciliation</h4>
              </div>
              <button
                type="button"
                class="close"
                data-bs-dismiss="modal"
                aria-label="Fermer"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body custom-modal-body">
              <form @submit.prevent="submitForm">
                <div class="mb-3">
                  <label class="form-label">Domiciliation</label>
                  <input type="text" class="form-control" value="Laptop" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Slug de domiciliation</label>
                  <input type="text" class="form-control" value="laptop" />
                </div>
                <div class="mb-0">
                  <div
                    class="status-toggle modal-status d-flex justify-content-between align-items-center"
                  >
                    <span class="status-label">Statut</span>
                    <input type="checkbox" id="domiciliation-status-edit" class="check" checked="" />
                    <label for="domiciliation-status-edit" class="checktoggle"></label>
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
                  <button type="submit" class="btn btn-submit">Enregistrer</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Modifier Domiciliation -->
</template>

<script>
export default {
  methods: {
    submitForm() {
      // Ajoutez ici la logique de soumission si besoin
      // Par exemple : this.$emit('saved')
    },
  },
}
</script>