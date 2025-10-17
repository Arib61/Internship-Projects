<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <!-- Enhanced Header Section -->
      <div class="page-header modern-header" style="background-color: #ABC270;">
        <div class="header-content">
          <div class="page-title-section">
            <div class="title-wrapper">
              <div class="icon-wrapper">
                <i class="fas fa-cog text-white"></i>
              </div>
              <div class="title-content">
                <h4 class="page-title">Paramètres de la société - {{ currentMode }}</h4>
                <p class="page-subtitle">Gérez les paramètres de votre société en toute simplicité</p>
              </div>
            </div>
          </div>

          <div class="header-actions">
            <!-- Edit Mode Indicator -->
           
           
          </div>
        </div>
      </div>

      <form @submit.prevent="submitForm" class="modern-form">
        <!-- Section: Informations de la société -->
        <div class="card modern-card mb-4">
          <div class="card-header modern-card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-building me-2 text-primary"></i>
              Informations de la société
            </h6>
          </div>
          <div class="card-body modern-card-body">
            <div class="form-section">
              <div class="row g-4">
                <!-- Nom de la société -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-building me-1"></i>
                      Nom de la société
                    </label>
                    <input id="societe_nom" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.societe_nom && isEditMode,
                      'is-valid': !errors.societe_nom && form.societe_nom && isEditMode
                    }" v-model="form.societe_nom" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('societe_nom', e.target.value)" @focus="checkEditAccess"
                      placeholder="Entrez le nom de la société" />
                    <div v-if="errors.societe_nom && isEditMode" class="invalid-feedback">
                      {{ errors.societe_nom }}
                    </div>
                  </div>
                </div>

                <!-- Adresse Siège Social Français -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-map-marker-alt me-1"></i>
                      Adresse Siège Social (FR)
                    </label>
                    <textarea id="adresse_siege_social_francais" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.adresse_siege_social_francais && isEditMode,
                      'is-valid': !errors.adresse_siege_social_francais && form.adresse_siege_social_francais && isEditMode
                    }" v-model="form.adresse_siege_social_francais" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('adresse_siege_social_francais', e.target.value)"
                      @focus="checkEditAccess" placeholder="Entrez l'adresse en français" rows="3"></textarea>
                    <div v-if="errors.adresse_siege_social_francais && isEditMode" class="invalid-feedback">
                      {{ errors.adresse_siege_social_francais }}
                    </div>
                  </div>
                </div>

                <!-- Adresse Siège Social Arabe -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-map-marker-alt me-1"></i>
                      Adresse Siège Social (AR)
                    </label>
                    <textarea id="adresse_siege_social_arabe" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.adresse_siege_social_arabe && isEditMode,
                      'is-valid': !errors.adresse_siege_social_arabe && form.adresse_siege_social_arabe && isEditMode
                    }" :value="form.adresse_siege_social_arabe" :readonly="!isEditMode"
                      @input="onInput('adresse_siege_social_arabe', $event)"
                      @focus="isEditMode ? onFocusKeyboard('adresse') : checkEditAccess()" style="direction: rtl"
                      placeholder="أدخل العنوان بالعربية" rows="3"></textarea>
                    <small class="form-text text-muted">Doit contenir du texte en arabe</small>
                    <div v-if="errors.adresse_siege_social_arabe && isEditMode" class="invalid-feedback">
                      {{ errors.adresse_siege_social_arabe }}
                    </div>
                    <div v-show="showAdresseKeyboard && isEditMode" class="keyboard-adresse mt-2"></div>
                  </div>
                </div>

                <!-- Téléphone -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-phone me-1"></i>
                      Téléphone
                    </label>
                    <input id="telephone" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.telephone && isEditMode,
                      'is-valid': !errors.telephone && form.telephone && isEditMode
                    }" v-model="form.telephone" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('telephone', e.target.value)" @focus="checkEditAccess"
                      placeholder="+212 6 12 34 56 78" />
                    <small class="form-text text-muted">Format: +212 6 12 34 56 78</small>
                    <div v-if="errors.telephone && isEditMode" class="invalid-feedback">
                      {{ errors.telephone }}
                    </div>
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-envelope me-1"></i>
                      Email
                    </label>
                    <input id="email" type="email" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.email && isEditMode,
                      'is-valid': !errors.email && form.email && isEditMode
                    }" v-model="form.email" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('email', e.target.value)" @focus="checkEditAccess"
                      placeholder="contact@example.com" />
                    <div v-if="errors.email && isEditMode" class="invalid-feedback">
                      {{ errors.email }}
                    </div>
                  </div>
                </div>

                <!-- Website -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-globe me-1"></i>
                      Site web
                    </label>
                    <input id="website" type="url" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.website && isEditMode,
                      'is-valid': !errors.website && form.website && isEditMode
                    }" v-model="form.website" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('website', e.target.value)" @focus="checkEditAccess"
                      placeholder="https://www.example.com" />
                    <small class="form-text text-muted">Optionnel</small>
                    <div v-if="errors.website && isEditMode" class="invalid-feedback">
                      {{ errors.website }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Informations du président -->
        <div class="card modern-card mb-4">
          <div class="card-header modern-card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-user-tie me-2 text-success"></i>
              Informations du président
            </h6>
          </div>
          <div class="card-body modern-card-body">
            <div class="form-section">
              <div class="row g-4">
                <!-- Date de naissance -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-calendar-alt me-1"></i>
                      Date de naissance
                    </label>
                    <input id="president_date_naissance" type="date" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.president_date_naissance && isEditMode,
                      'is-valid': !errors.president_date_naissance && form.president_date_naissance && isEditMode
                    }" v-model="form.president_date_naissance" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('president_date_naissance', e.target.value)"
                      @focus="checkEditAccess" />
                    <small class="form-text text-muted">Âge entre 18 et 100 ans</small>
                    <div v-if="errors.president_date_naissance && isEditMode" class="invalid-feedback">
                      {{ errors.president_date_naissance }}
                    </div>
                  </div>
                </div>

                <!-- CIN -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-id-card me-1"></i>
                      CIN
                    </label>
                    <input id="president_cin" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.president_cin && isEditMode,
                      'is-valid': !errors.president_cin && form.president_cin && isEditMode
                    }" v-model="form.president_cin" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('president_cin', e.target.value)" @focus="checkEditAccess"
                      placeholder="AB123456" maxlength="9" />
                    <small class="form-text text-muted">Format: AB123456</small>
                    <div v-if="errors.president_cin && isEditMode" class="invalid-feedback">
                      {{ errors.president_cin }}
                    </div>
                  </div>
                </div>

                <!-- Adresse du président -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-home me-1"></i>
                      Adresse
                    </label>
                    <textarea id="president_adresse" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.president_adresse && isEditMode,
                      'is-valid': !errors.president_adresse && form.president_adresse && isEditMode
                    }" v-model="form.president_adresse" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('president_adresse', e.target.value)" @focus="checkEditAccess"
                      placeholder="Adresse du président" rows="3"></textarea>
                    <div v-if="errors.president_adresse && isEditMode" class="invalid-feedback">
                      {{ errors.president_adresse }}
                    </div>
                  </div>
                </div>

                <!-- Nom Complet Français -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user me-1"></i>
                      Nom Complet (FR)
                    </label>
                    <input id="nom_complet_francais" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.nom_complet_francais && isEditMode,
                      'is-valid': !errors.nom_complet_francais && form.nom_complet_francais && isEditMode
                    }" v-model="form.nom_complet_francais" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('nom_complet_francais', e.target.value)" @focus="checkEditAccess"
                      placeholder="Nom complet en français" />
                    <div v-if="errors.nom_complet_francais && isEditMode" class="invalid-feedback">
                      {{ errors.nom_complet_francais }}
                    </div>
                  </div>
                </div>

                <!-- Nom Complet Arabe -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-user me-1"></i>
                      Nom Complet (AR)
                    </label>
                    <input id="nom_complet_arabe" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.nom_complet_arabe && isEditMode,
                      'is-valid': !errors.nom_complet_arabe && form.nom_complet_arabe && isEditMode
                    }" :value="form.nom_complet_arabe" :readonly="!isEditMode"
                      @input="onInput('nom_complet_arabe', $event)"
                      @focus="isEditMode ? onFocusKeyboard('ar') : checkEditAccess()" style="direction: rtl"
                      placeholder="الاسم الكامل بالعربية" />
                    <small class="form-text text-muted">Doit contenir du texte en arabe</small>
                    <div v-if="errors.nom_complet_arabe && isEditMode" class="invalid-feedback">
                      {{ errors.nom_complet_arabe }}
                    </div>
                    <div v-show="showArKeyboard && isEditMode" class="keyboard-ar mt-2"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Images de la société -->
        <div class="card modern-card mb-4">
          <div class="card-header modern-card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-images me-2 text-warning"></i>
              Images de la société
            </h6>
          </div>
          <div class="card-body modern-card-body">
            <div class="form-section">
              <div class="row g-4">
                <!-- Logo -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-image me-1"></i>
                      Logo
                    </label>
                    <div class="upload-section">
                      <input id="logo" type="file" class="form-control d-none"
                        @change="handleFileUpload($event, 'logo')" accept="image/*" />
                      <button type="button" class="btn modern-btn" :class="isEditMode ? 'btn-primary' : 'btn-secondary'"
                        :disabled="!isEditMode" @click="triggerFileInput('logo')">
                        <i class="fas fa-upload me-1"></i>
                        Télécharger Logo
                      </button>
                      <span class="ms-2 text-muted" v-if="form.logo">{{ form.logo.name }}</span>
                      <span class="ms-2 text-muted" v-else>Aucun fichier sélectionné</span>
                    </div>
                    <small class="form-text text-muted">Format recommandé: PNG, JPG. Max 5MB</small>

                    <!-- Logo Preview -->
                    <div v-if="logoPreview" class="mt-3">
                      <div class="image-preview-card">
                        <div class="preview-header">
                          <span class="preview-title">Aperçu Logo</span>
                          <button type="button" class="btn btn-sm btn-danger" :disabled="!isEditMode"
                            @click="removeImage('logo')">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </div>
                        <div class="preview-content">
                          <img :src="logoPreview" alt="Logo Preview" class="preview-image logo-preview"
                            @error="logoPreview = null" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Icon -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-star me-1"></i>
                      Icône
                    </label>
                    <div class="upload-section">
                      <input id="icon" type="file" class="form-control d-none"
                        @change="handleFileUpload($event, 'icon')" accept="image/*" />
                      <button type="button" class="btn modern-btn" :class="isEditMode ? 'btn-primary' : 'btn-secondary'"
                        :disabled="!isEditMode" @click="triggerFileInput('icon')">
                        <i class="fas fa-upload me-1"></i>
                        Télécharger Icône
                      </button>
                      <span class="ms-2 text-muted" v-if="form.icon">{{ form.icon.name }}</span>
                      <span class="ms-2 text-muted" v-else>Aucun fichier sélectionné</span>
                    </div>
                    <small class="form-text text-muted">Format recommandé: PNG, ICO. Max 5MB</small>

                    <!-- Icon Preview -->
                    <div v-if="iconPreview" class="mt-3">
                      <div class="image-preview-card icon-card">
                        <div class="preview-header">
                          <span class="preview-title">Aperçu Icône</span>
                          <button type="button" class="btn btn-sm btn-danger" :disabled="!isEditMode"
                            @click="removeImage('icon')">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </div>
                        <div class="preview-content">
                          <img :src="iconPreview" alt="Icon Preview" class="preview-image icon-preview"
                            @error="iconPreview = null" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Informations légales -->
        <div class="card modern-card mb-4">
          <div class="card-header modern-card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-gavel me-2 text-info"></i>
              Informations légales
            </h6>
          </div>
          <div class="card-body modern-card-body">
            <div class="form-section">
              <div class="row g-4">
                <!-- Capital Social -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-coins me-1"></i>
                      Capital Social (MAD)
                    </label>
                    <div class="input-group">
                      <input id="capital_social" type="text" class="form-control modern-input" :class="{
                        'form-control-readonly': !isEditMode,
                        'is-invalid': errors.capital_social && isEditMode,
                        'is-valid': !errors.capital_social && form.capital_social && isEditMode
                      }" v-model="form.capital_social" :readonly="!isEditMode" placeholder="100 000,00"
                        @input="(e) => handleInputChange('capital_social', e.target.value)" @focus="checkEditAccess"
                        autocomplete="off" />
                      <span class="input-group-text">DH</span>
                    </div>
                    <small class="form-text text-muted">Format: 100 000,00 (espaces pour milliers, virgule pour
                      décimales)</small>
                    <div v-if="errors.capital_social && isEditMode" class="invalid-feedback">
                      {{ errors.capital_social }}
                    </div>
                  </div>
                </div>

                <!-- ICE -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-barcode me-1"></i>
                      ICE
                    </label>
                    <input id="ice" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.ice && isEditMode,
                      'is-valid': !errors.ice && form.ice && isEditMode
                    }" v-model="form.ice" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('ice', e.target.value)" @focus="checkEditAccess"
                      placeholder="000000000000000" maxlength="15" />
                    <small class="form-text text-muted">15 chiffres exactement</small>
                    <div v-if="errors.ice && isEditMode" class="invalid-feedback">
                      {{ errors.ice }}
                    </div>
                  </div>
                </div>

                <!-- RC -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-file-alt me-1"></i>
                      Registre de Commerce (RC)
                    </label>
                    <input id="rc" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.rc && isEditMode,
                      'is-valid': !errors.rc && form.rc && isEditMode
                    }" v-model="form.rc" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('rc', e.target.value)" @focus="checkEditAccess"
                      placeholder="000000" maxlength="8" />
                    <small class="form-text text-muted">6 à 8 chiffres</small>
                    <div v-if="errors.rc && isEditMode" class="invalid-feedback">
                      {{ errors.rc }}
                    </div>
                  </div>
                </div>

                <!-- Identifiant Fiscal -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-receipt me-1"></i>
                      Identifiant Fiscal
                    </label>
                    <input id="identifiant_fiscal" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.identifiant_fiscal && isEditMode,
                      'is-valid': !errors.identifiant_fiscal && form.identifiant_fiscal && isEditMode
                    }" v-model="form.identifiant_fiscal" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('identifiant_fiscal', e.target.value)" @focus="checkEditAccess"
                      placeholder="00000000" maxlength="8" />
                    <small class="form-text text-muted">8 chiffres exactement</small>
                    <div v-if="errors.identifiant_fiscal && isEditMode" class="invalid-feedback">
                      {{ errors.identifiant_fiscal }}
                    </div>
                  </div>
                </div>

                <!-- TP -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label required">
                      <i class="fas fa-percentage me-1"></i>
                      Taxe Professionnelle (TP)
                    </label>
                    <input id="tp" type="text" class="form-control modern-input" :class="{
                      'form-control-readonly': !isEditMode,
                      'is-invalid': errors.tp && isEditMode,
                      'is-valid': !errors.tp && form.tp && isEditMode
                    }" v-model="form.tp" :readonly="!isEditMode"
                      @input="(e) => handleInputChange('tp', e.target.value)" @focus="checkEditAccess"
                      placeholder="00000000" maxlength="8" />
                    <small class="form-text text-muted">8 chiffres exactement</small>
                    <div v-if="errors.tp && isEditMode" class="invalid-feedback">
                      {{ errors.tp }}
                    </div>
                  </div>
                </div>

                <!-- Type d'entreprise et Type de client -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-industry me-1"></i>
                      Type d'entreprise
                    </label>
                    <select class="form-select modern-input" v-model="form.type_entreprise" :disabled="!isEditMode">
                      <option value="MORALE">Morale</option>
                      <option value="PHYSIQUE">Physique</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">
                      <i class="fas fa-users me-1"></i>
                      Type de client
                    </label>
                    <select class="form-select modern-input" v-model="form.type_client" :disabled="!isEditMode">
                      <option v-for="type in filteredClientTypes" :key="type" :value="type">
                        {{ type }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-section">
          <div class="action-buttons">
            <!-- Edit Button -->
            <button v-if="!isEditMode" type="button" class="btn btn-warning modern-btn action-btn"
              @click="enableEditMode">
              <i class="fas fa-edit me-1"></i>
              Éditer
            </button>

            <!-- Cancel Button -->
            <button v-if="isEditMode" type="button" class="btn btn-secondary modern-btn action-btn" @click="cancelEdit">
              <i class="fas fa-times me-1"></i>
              Annuler
            </button>

            <!-- Save Button -->
            <button type="submit" class="btn modern-btn action-btn"
              :class="isEditMode ? (isFormValid ? 'btn-success' : 'btn-danger') : 'btn-secondary'"
              :disabled="!isEditMode || !isFormValid">
              <i class="fas fa-save me-1"></i>
              Enregistrer
            </button>
          </div>

          <!-- Validation Summary -->
          <div v-if="isEditMode && !isFormValid" class="validation-alert">
            <div class="alert alert-warning modern-alert">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Attention:</strong> Veuillez corriger les erreurs de validation avant de sauvegarder.
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";
import Keyboard from "simple-keyboard";
import "simple-keyboard/build/css/index.css";
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: 'ModernSettings',
  setup() {
    const form = ref({
      // Informations de la société
      societe_nom: "MAEXPERTISE CONSULTING",
      adresse_siege_social_francais: "44, AVENUE DE FRANCE, 3EME ETAGE, APPRT N° 16, AGDAL-RABAT",
      adresse_siege_social_arabe: "44، شارع فرنسا، الطابق الثالث، الشقة رقم 16، أكدال-الرباط",
      telephone: "0537777604",
      email: "contact@meconsulting.ma",
      website: "https://meconsulting.ma/",
      logo: null,
      icon: null,

      // Informations du président
      president_date_naissance: "2001-01-01",
      president_cin: "AD1234567",
      president_adresse: "44, AVENUE DE FRANCE, 3EME ETAGE, APPRT N° 16, AGDAL-RABAT",
      nom_complet_francais: "MOURAD EL MANSOURI",
      nom_complet_arabe: "مراد المنصوري",

      // Informations légales
      capital_social: "100 000,00",
      ice: "003378206000060",
      rc: "172295",
      identifiant_fiscal: "54001301",
      tp: "25742361",
      type_entreprise: "MORALE",
      type_client: "SARL",
    });

    // Validation errors
    const errors = ref({
      // Informations de la société
      societe_nom: "",
      adresse_siege_social_francais: "",
      adresse_siege_social_arabe: "",
      telephone: "",
      email: "",
      website: "",

      // Informations du président
      president_date_naissance: "",
      president_cin: "",
      president_adresse: "",
      nom_complet_francais: "",
      nom_complet_arabe: "",

      // Informations légales
      capital_social: "",
      ice: "",
      rc: "",
      identifiant_fiscal: "",
      tp: "",
    });

    const societeId = ref(null);
    const isEditMode = ref(false);

    // For image previews
    const logoPreview = ref(null);
    const iconPreview = ref(null);

    // Arabic keyboard states
    const showArKeyboard = ref(false);
    const showAdresseKeyboard = ref(false);
    const showPresidentAdresseKeyboard = ref(false);
    let keyboardAr = null;
    let keyboardAdresse = null;
    let keyboardPresidentAdresse = null;

    const filteredClientTypes = computed(() => {
      if (form.value.type_entreprise === "MORALE") {
        return ["SARL", "SARL AU", "SA", "SNC", "ASSOCIATION", "COOPERATIVE", "SAS", "SASU", "GIE", "SCP", "SCI"]
      } else if (form.value.type_entreprise === "PHYSIQUE") {
        return ["ENTREPRISE INDIVIDUELLE", "AUTO-ENTREPRENEUR"]
      }
      return []
    })

    // Validation functions
    const validateRequired = (value, fieldName) => {
      if (!value || value.toString().trim() === "") {
        return `${fieldName} est requis`;
      }
      return "";
    };

    const validateArabicText = (text, fieldName) => {
      if (!text || text.trim() === "") {
        return `${fieldName} est requis`;
      }

      const arabicRegex = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
      if (!arabicRegex.test(text)) {
        return `${fieldName} doit contenir du texte en arabe`;
      }

      if (text.trim().length < 2) {
        return `${fieldName} doit contenir au moins 2 caractères`;
      }

      return "";
    };

    const validateEmail = (email) => {
      if (!email || email.trim() === "") {
        return "L'email est requis";
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        return "Format d'email invalide";
      }

      return "";
    };

    const validatePhone = (phone) => {
      if (!phone || phone.trim() === "") {
        return "Le téléphone est requis";
      }

      const phoneRegex = /^(\+212|0)[5-7][0-9]{8}$|^[0-9]{9,10}$/;
      if (!phoneRegex.test(phone.replace(/[\s-]/g, ''))) {
        return "Format de téléphone invalide (ex: +212 6 12 34 56 78)";
      }

      return "";
    };

    const validateWebsite = (website) => {
      if (!website || website.trim() === "") {
        return "";
      }

      try {
        new URL(website);
        return "";
      } catch {
        return "Format d'URL invalide (ex: https://www.example.com)";
      }
    };

    const validateDate = (date, fieldName) => {
      if (!date || date.trim() === "") {
        return `${fieldName} est requise`;
      }

      const dateObj = new Date(date);
      if (isNaN(dateObj.getTime())) {
        return "Format de date invalide";
      }

      const today = new Date();
      const age = today.getFullYear() - dateObj.getFullYear();
      if (age < 18 || age > 100) {
        return "L'âge doit être entre 18 et 100 ans";
      }

      return "";
    };

    // Fixed formatNumber function for capital social
    const formatNumber = (value) => {
      if (!value && value !== 0) return "";

      // Convert to string and remove any existing formatting
      let cleanValue = value.toString().replace(/[^\d.,]/g, '');

      // Handle comma as decimal separator
      cleanValue = cleanValue.replace(',', '.');

      // Parse as float
      const numValue = parseFloat(cleanValue);

      if (isNaN(numValue)) return "";

      // Format with 2 decimal places and French formatting
      return numValue.toLocaleString('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    // Function to convert formatted number back to decimal for API
    const parseFormattedNumber = (formattedValue) => {
      if (!formattedValue) return 0;
      // Remove spaces and replace comma with dot for parsing
      return parseFloat(formattedValue.toString().replace(/\s/g, '').replace(',', '.')) || 0;
    };

    const validateCIN = (cin) => {
      if (!cin || cin.trim() === "") {
        return "La CIN est requise";
      }

      // Moroccan CIN format: 1-2 letters followed by 6-7 digits
      const cinRegex = /^[A-Za-z]{1,2}[0-9]{6,7}$/;
      if (!cinRegex.test(cin)) {
        return "Format CIN invalide (ex: AB123456)";
      }

      return "";
    };

    // Fixed validateCapitalSocial function
    const validateCapitalSocial = (value) => {
      if (!value || value.toString().trim() === "") {
        return "Le capital social est requis";
      }

      // Parse the formatted value to get the numeric value
      const numericValue = parseFormattedNumber(value);

      if (isNaN(numericValue) || numericValue <= 0) {
        return "Le capital social doit être un nombre positif";
      }

      if (numericValue < 10000) {
        return "Le capital social minimum est de 10 000,00 DH";
      }

      return "";
    };

    const validateICE = (ice) => {
      if (!ice || ice.trim() === "") {
        return "L'ICE est requis";
      }
      if (!/^\d{15}$/.test(ice)) {
        return "L'ICE doit contenir exactement 15 chiffres";
      }
      return "";
    };

    const validateRC = (rc) => {
      if (!rc || rc.trim() === "") {
        return "Le RC est requis";
      }
      if (!/^\d{6,8}$/.test(rc)) {
        return "Le RC doit contenir entre 6 et 8 chiffres";
      }
      return "";
    };

    const validateIdentifiantFiscal = (if_value) => {
      if (!if_value || if_value.trim() === "") {
        return "L'Identifiant Fiscal est requis";
      }
      if (!/^\d{8}$/.test(if_value)) {
        return "L'Identifiant Fiscal doit contenir exactement 8 chiffres";
      }
      return "";
    };

    const validateTP = (tp) => {
      if (!tp || tp.trim() === "") {
        return "La Taxe Professionnelle est requise";
      }
      if (!/^\d{8}$/.test(tp)) {
        return "La Taxe Professionnelle doit contenir exactement 8 chiffres";
      }
      return "";
    };

    // Validate all fields
    const validateAllFields = () => {
      // Informations de la société
      errors.value.societe_nom = validateRequired(form.value.societe_nom, "Le nom de la société");
      errors.value.adresse_siege_social_francais = validateRequired(form.value.adresse_siege_social_francais, "L'adresse en français");
      errors.value.adresse_siege_social_arabe = validateArabicText(form.value.adresse_siege_social_arabe, "L'adresse en arabe");
      errors.value.telephone = validatePhone(form.value.telephone);
      errors.value.email = validateEmail(form.value.email);
      errors.value.website = validateWebsite(form.value.website);

      // Informations du président
      errors.value.president_date_naissance = validateDate(form.value.president_date_naissance, "La date de naissance");
      errors.value.president_cin = validateCIN(form.value.president_cin);
      errors.value.president_adresse = validateRequired(form.value.president_adresse, "L'adresse du président");
      errors.value.nom_complet_francais = validateRequired(form.value.nom_complet_francais, "Le nom complet en français");
      errors.value.nom_complet_arabe = validateArabicText(form.value.nom_complet_arabe, "Le nom complet en arabe");

      // Informations légales
      errors.value.capital_social = validateCapitalSocial(form.value.capital_social);
      errors.value.ice = validateICE(form.value.ice);
      errors.value.rc = validateRC(form.value.rc);
      errors.value.identifiant_fiscal = validateIdentifiantFiscal(form.value.identifiant_fiscal);
      errors.value.tp = validateTP(form.value.tp);
    };

    // Computed property to check if form is valid
    const isFormValid = computed(() => {
      return Object.values(errors.value).every(error => error === "");
    });

    // Add this computed property after the isFormValid computed property
    const currentMode = computed(() => {
      return societeId.value ? 'Mise à jour' : 'Création';
    });

    // Input handlers with validation
    const handleInputChange = (field, value) => {
      if (!isEditMode.value) return;

      let processedValue = value;
      let validationResult = "";

      switch (field) {
        case "societe_nom":
          validationResult = validateRequired(value, "Le nom de la société");
          break;
        case "adresse_siege_social_francais":
          validationResult = validateRequired(value, "L'adresse en français");
          break;
        case "adresse_siege_social_arabe":
          validationResult = validateArabicText(value, "L'adresse en arabe");
          break;
        case "telephone":
          validationResult = validatePhone(value);
          break;
        case "email":
          validationResult = validateEmail(value);
          break;
        case "website":
          validationResult = validateWebsite(value);
          break;
        case "president_date_naissance":
          validationResult = validateDate(value, "La date de naissance");
          break;
        case "president_cin":
          processedValue = value.toUpperCase();
          validationResult = validateCIN(processedValue);
          break;
        case "president_adresse":
          validationResult = validateRequired(value, "L'adresse du président");
          break;
        case "nom_complet_francais":
          validationResult = validateRequired(value, "Le nom complet en français");
          break;
        case "nom_complet_arabe":
          validationResult = validateArabicText(value, "Le nom complet en arabe");
          break;
        case "capital_social":
          // Handle capital social formatting
          if (value) {
            // Remove all non-numeric characters except comma and dot
            let cleanValue = value.toString().replace(/[^\d.,]/g, '');

            // Convert comma to dot for parsing
            cleanValue = cleanValue.replace(',', '.');

            // Parse and reformat
            const numValue = parseFloat(cleanValue);
            if (!isNaN(numValue)) {
              processedValue = formatNumber(numValue);
            } else {
              processedValue = "";
            }
          }
          validationResult = validateCapitalSocial(processedValue);
          break;
        case "ice":
          processedValue = value.replace(/\D/g, '');
          if (processedValue.length > 15) processedValue = processedValue.slice(0, 15);
          validationResult = validateICE(processedValue);
          break;
        case "rc":
          processedValue = value.replace(/\D/g, '');
          if (processedValue.length > 8) processedValue = processedValue.slice(0, 8);
          validationResult = validateRC(processedValue);
          break;
        case "identifiant_fiscal":
          processedValue = value.replace(/\D/g, '');
          if (processedValue.length > 8) processedValue = processedValue.slice(0, 8);
          validationResult = validateIdentifiantFiscal(processedValue);
          break;
        case "tp":
          processedValue = value.replace(/\D/g, '');
          if (processedValue.length > 8) processedValue = processedValue.slice(0, 8);
          validationResult = validateTP(processedValue);
          break;
        default:
          break;
      }

      form.value[field] = processedValue;
      errors.value[field] = validationResult;
    };

    // Function to get image URL from Laravel Storage
    function getImageUrl(path) {
      if (!path || path === 'null') return null;
      if (path.startsWith('data:') || path.startsWith('http')) return path;
      return `http://localhost:8000/storage/${path}`;
    }

    // Fetch société data and determine create/update mode
    onMounted(async () => {
      document.addEventListener("mousedown", handleClickOutside);

      try {
        const res = await axios.get("/societes");
        console.log("Société data:", res.data);

        // Check if valid société data exists
        if (res.data && res.data.id) {
          // Update mode - société exists
          societeId.value = res.data.id;
          Object.keys(form.value).forEach((key) => {
            if (res.data[key] !== undefined && key !== "logo" && key !== "icon") {
              form.value[key] = res.data[key];
            }
          });

          // Handle capital social formatting on load
          if (form.value.capital_social) {
            const numValue = parseFormattedNumber(form.value.capital_social);
            form.value.capital_social = formatNumber(numValue);
          }

          // Handle images
          if (res.data.logo && res.data.logo !== "null") {
            logoPreview.value = getImageUrl(res.data.logo);
          } else {
            logoPreview.value = null;
          }

          if (res.data.icon && res.data.icon !== "null") {
            iconPreview.value = getImageUrl(res.data.icon);
          } else {
            iconPreview.value = null;
          }

          validateAllFields();
          console.log("Mode: Update - Société ID:", societeId.value);
        } else {
          // Create mode - no société exists
          societeId.value = null;
          console.log("Mode: Create - No existing société found");
        }
      } catch (e) {
        // Create mode - error fetching data (likely 404 or no société exists)
        societeId.value = null;
        console.log("Mode: Create - Error fetching société data:", e);
      }
    });

    onBeforeUnmount(() => {
      document.removeEventListener("mousedown", handleClickOutside);
    });

    function enableEditMode() {
      isEditMode.value = true;
      validateAllFields();
      Swal.fire({
        icon: "info",
        title: "Mode édition activé",
        text: "Vous pouvez maintenant modifier les informations",
        timer: 1500,
        showConfirmButton: false,
        toast: true,
        position: "top-end",
      });
    }

    function cancelEdit() {
      Swal.fire({
        title: "Annuler les modifications?",
        text: "Toutes les modifications non sauvegardées seront perdues.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Oui, annuler",
        cancelButtonText: "Non, continuer"
      }).then((result) => {
        if (result.isConfirmed) {
          isEditMode.value = false;
          location.reload();
        }
      });
    }

    function onInput(field, event) {
      if (!isEditMode.value) return;

      const value = event.target.value;
      handleInputChange(field, value);

      if (field === "nom_complet_arabe") {
        if (keyboardAr) keyboardAr.setInput(form.value.nom_complet_arabe);
      } else if (field === "adresse_siege_social_arabe") {
        if (keyboardAdresse) keyboardAdresse.setInput(form.value.adresse_siege_social_arabe);
      } else if (field === "president_adresse") {
        if (keyboardPresidentAdresse) keyboardPresidentAdresse.setInput(form.value.president_adresse);
      }
    }

    function onFocusKeyboard(type) {
      if (!isEditMode.value) return;

      if (type === "ar") {
        showArKeyboard.value = true;
        if (!keyboardAr) {
          setTimeout(() => {
            keyboardAr = new Keyboard(".keyboard-ar", {
              layout: {
                default: [
                  "ض ص ث ق ف غ ع ه خ ح ج د",
                  "ش س ي ب ل ا ت ن م ك ط",
                  "ئ ء ؤ ر لا ى ة و ز ظ",
                  "{space}",
                ],
              },
              display: {
                "{space}": "مسافة",
              },
              rtl: true,
              onChange: (input) => {
                handleInputChange("nom_complet_arabe", input);
              },
              inputName: "nom_complet_arabe",
            });
            keyboardAr.setInput(form.value.nom_complet_arabe);
          }, 0);
        }
      } else if (type === "adresse") {
        showAdresseKeyboard.value = true;
        if (!keyboardAdresse) {
          setTimeout(() => {
            keyboardAdresse = new Keyboard(".keyboard-adresse", {
              layout: {
                default: [
                  "ض ص ث ق ف غ ع ه خ ح ج د",
                  "ش س ي ب ل ا ت ن م ك ط",
                  "ئ ء ؤ ر لا ى ة و ز ظ",
                  "{space}",
                ],
              },
              display: {
                "{space}": "مسافة",
              },
              rtl: true,
              onChange: (input) => {
                handleInputChange("adresse_siege_social_arabe", input);
              },
              inputName: "adresse_siege_social_arabe",
            });
            keyboardAdresse.setInput(form.value.adresse_siege_social_arabe);
          }, 0);
        }
      } else if (type === "president_adresse") {
        showPresidentAdresseKeyboard.value = true;
        if (!keyboardPresidentAdresse) {
          setTimeout(() => {
            keyboardPresidentAdresse = new Keyboard(".keyboard-president-adresse", {
              layout: {
                default: [
                  "ض ص ث ق ف غ ع ه خ ح ج د",
                  "ش س ي ب ل ا ت ن م ك ط",
                  "ئ ء ؤ ر لا ى ة و ز ظ",
                  "{space}",
                ],
              },
              display: {
                "{space}": "مسافة",
              },
              rtl: true,
              onChange: (input) => {
                handleInputChange("president_adresse", input);
              },
              inputName: "president_adresse",
            });
            keyboardPresidentAdresse.setInput(form.value.president_adresse);
          }, 0);
        }
      }
    }

    function handleClickOutside(e) {
      const arInput = document.getElementById("nom_complet_arabe");
      const arKeyboard = document.querySelector(".keyboard-ar");
      if (
        showArKeyboard.value &&
        arInput &&
        arKeyboard &&
        !arInput.contains(e.target) &&
        !arKeyboard.contains(e.target)
      ) {
        showArKeyboard.value = false;
      }

      const adresseInput = document.getElementById("adresse_siege_social_arabe");
      const adresseKeyboard = document.querySelector(".keyboard-adresse");
      if (
        showAdresseKeyboard.value &&
        adresseInput &&
        adresseKeyboard &&
        !adresseInput.contains(e.target) &&
        !adresseKeyboard.contains(e.target)
      ) {
        showAdresseKeyboard.value = false;
      }

      const presidentAdresseInput = document.getElementById("president_adresse");
      const presidentAdresseKeyboard = document.querySelector(".keyboard-president-adresse");
      if (
        showPresidentAdresseKeyboard.value &&
        presidentAdresseInput &&
        presidentAdresseKeyboard &&
        !presidentAdresseInput.contains(e.target) &&
        !presidentAdresseKeyboard.contains(e.target)
      ) {
        showPresidentAdresseKeyboard.value = false;
      }
    }

    function triggerFileInput(inputId) {
      if (!isEditMode.value) {
        Swal.fire({
          icon: "warning",
          title: "Accès refusé",
          text: "Veuillez cliquer sur 'Éditer' pour modifier les informations",
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: "top-end",
        });
        return;
      }
      document.getElementById(inputId).click();
    }

    function handleFileUpload(event, field) {
      if (!isEditMode.value) return;

      const file = event.target.files[0];
      if (file && file.size <= 5 * 1024 * 1024) {
        form.value[field] = file;

        const reader = new FileReader();
        reader.onload = (e) => {
          if (field === "logo") {
            logoPreview.value = e.target.result;
          } else if (field === "icon") {
            iconPreview.value = e.target.result;
          }
        };
        reader.readAsDataURL(file);
      } else {
        alert("Fichier trop volumineux (max 5MB)");
        event.target.value = null;
      }
    }

    function removeImage(field) {
      if (!isEditMode.value) return;

      form.value[field] = null;
      if (field === "logo") {
        logoPreview.value = null;
      } else if (field === "icon") {
        iconPreview.value = null;
      }
      const fileInput = document.getElementById(field);
      if (fileInput) fileInput.value = "";
    }

    async function submitForm() {
      if (!isEditMode.value) {
        Swal.fire({
          icon: "warning",
          title: "Accès refusé",
          text: "Veuillez cliquer sur 'Éditer' pour modifier les informations",
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: "top-end",
        });
        return;
      }

      validateAllFields();

      if (!isFormValid.value) {
        const errorFields = Object.keys(errors.value).filter(key => errors.value[key] !== "");
        Swal.fire({
          icon: "error",
          title: "Erreurs de validation",
          html: `Veuillez corriger les erreurs suivantes:<br><ul style="text-align: left; margin: 10px 0;">${errorFields.map(field => `<li>${errors.value[field]}</li>`).join('')}</ul>`,
          confirmButtonText: "Compris",
        });
        return;
      }

      try {
        const formData = new FormData();
        Object.keys(form.value).forEach((key) => {
          if (form.value[key] !== null && form.value[key] !== undefined) {
            // Convert formatted capital social back to numeric value for API
            if (key === 'capital_social') {
              formData.append(key, parseFormattedNumber(form.value[key]));
            } else {
              formData.append(key, form.value[key]);
            }
          }
        });

        let response;
        let method;

        if (societeId.value) {
          // Update existing société
          response = await axios.post(`/societes/${societeId.value}?_method=PUT`, formData, {
            headers: { "Content-Type": "multipart/form-data" },
          });
          method = "update";
        } else {
          // Create new société
          response = await axios.post(`/societes`, formData, {
            headers: { "Content-Type": "multipart/form-data" },
          });
          method = "create";

          // Set the société ID for future updates
          if (response.data?.id) {
            societeId.value = response.data.id;
          }
        }

        // Update image previews if returned from API
        if (response.data?.logo) {
          logoPreview.value = getImageUrl(response.data.logo);
        }
        if (response.data?.icon) {
          iconPreview.value = getImageUrl(response.data.icon);
        }

        isEditMode.value = false;

        Swal.fire({
          icon: "success",
          title: "Succès!",
          text: method === "create" ? "Société créée avec succès!" : "Société mise à jour avec succès!",
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: "top-end",
        });

      } catch (error) {
        console.error("Erreur lors de la soumission:", error);

        let errorMessage = "Une erreur est survenue lors de la sauvegarde";

        if (error.response?.data?.errors) {
          const serverErrors = error.response.data.errors;
          errorMessage = Object.values(serverErrors).flat().join('<br>');
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        }

        Swal.fire({
          icon: "error",
          title: "Erreur de sauvegarde",
          html: errorMessage,
          confirmButtonText: "Compris",
        });
      }
    }

    function checkEditAccess() {
      if (!isEditMode.value) {
        Swal.fire({
          icon: "warning",
          title: "Accès refusé",
          text: "Veuillez cliquer sur 'Éditer' pour modifier les informations",
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: "top-end",
        });
      }
    }

    // Watch for type_entreprise changes to update type_client
    watch(() => form.value.type_entreprise, (newValue) => {
      if (newValue === "MORALE" && !["SARL", "SARL AU", "SA", "SNC", "ASSOCIATION", "COOPERATIVE", "SAS", "SASU", "GIE", "SCP", "SCI"].includes(form.value.type_client)) {
        form.value.type_client = "SARL";
      } else if (newValue === "PHYSIQUE" && !["ENTREPRISE INDIVIDUELLE", "AUTO-ENTREPRENEUR"].includes(form.value.type_client)) {
        form.value.type_client = "ENTREPRISE INDIVIDUELLE";
      }
    });

    return {
      form,
      errors,
      isEditMode,
      logoPreview,
      iconPreview,
      showArKeyboard,
      showAdresseKeyboard,
      showPresidentAdresseKeyboard,
      isFormValid,
      currentMode,
      filteredClientTypes,
      enableEditMode,
      cancelEdit,
      onInput,
      onFocusKeyboard,
      triggerFileInput,
      handleFileUpload,
      removeImage,
      submitForm,
      checkEditAccess,
      handleInputChange,
    };
  },
};
</script>

<style scoped>
/* Modern Header Styles */
.modern-header {
  background: linear-gradient(135deg, #ABC270 0%, #8FA55C 100%);
  border-radius: 12px;
  margin-bottom: 2rem;
  box-shadow: 0 4px 20px rgba(171, 194, 112, 0.3);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
}

.title-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.icon-wrapper {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 12px;
  backdrop-filter: blur(10px);
}

.icon-wrapper i {
  font-size: 1.5rem;
}

.title-content h4 {
  color: white;
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.page-subtitle {
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  font-size: 0.95rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.mode-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
}

/* Modern Card Styles */
.modern-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
}

.modern-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.modern-card-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #dee2e6;
  padding: 1.25rem 1.5rem;
}

.modern-card-header .card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #495057;
  display: flex;
  align-items: center;
}

.modern-card-body {
  padding: 2rem 1.5rem;
}

/* Modern Form Styles */
.modern-form {
  max-width: 100%;
}

.form-section {
  position: relative;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  font-size: 0.95rem;
}

.form-label.required::after {
  content: " *";
  color: #dc3545;
  font-weight: bold;
}

.form-label i {
  color: #6c757d;
  margin-right: 0.5rem;
}

.modern-input {
  border: 2px solid #e9ecef;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background-color: #fff;
}

.modern-input:focus {
  border-color: #ABC270;
  box-shadow: 0 0 0 0.2rem rgba(171, 194, 112, 0.25);
  background-color: #fff;
}

.modern-input:hover:not(:focus):not(.form-control-readonly) {
  border-color: #ced4da;
}

.form-control-readonly {
  background-color: #f8f9fa;
  border-color: #e9ecef;
  color: #6c757d;
  cursor: not-allowed;
}

.form-control-readonly:focus {
  background-color: #f8f9fa;
  border-color: #e9ecef;
  box-shadow: none;
}

/* Validation Styles */
.is-valid {
  border-color: #28a745;
}

.is-valid:focus {
  border-color: #28a745;
  box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.is-invalid {
  border-color: #dc3545;
}

.is-invalid:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #dc3545;
}

/* Modern Button Styles */
.modern-btn {
  border-radius: 10px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.modern-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modern-btn:active {
  transform: translateY(0);
}

.modern-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Upload Section Styles */
.upload-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

/* Image Preview Styles */
.image-preview-card {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  transition: all 0.3s ease;
}

.image-preview-card:hover {
  border-color: #ABC270;
  box-shadow: 0 4px 12px rgba(171, 194, 112, 0.2);
}

.preview-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 0.75rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #dee2e6;
}

.preview-title {
  font-weight: 600;
  color: #495057;
  font-size: 0.9rem;
}

.preview-content {
  padding: 1rem;
  text-align: center;
}

.preview-image {
  max-width: 100%;
  max-height: 150px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.logo-preview {
  max-height: 120px;
}

.icon-preview {
  max-height: 80px;
  max-width: 80px;
}

.icon-card .preview-content {
  padding: 0.75rem;
}

/* Action Section Styles */
.action-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px solid #e9ecef;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

.action-btn {
  min-width: 140px;
  justify-content: center;
}

/* Validation Alert Styles */
.validation-alert {
  margin-top: 1rem;
}

.modern-alert {
  border: none;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
}

.alert-warning {
  background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
  color: #856404;
  border-left: 4px solid #ffc107;
}

/* Keyboard Styles */
.keyboard-ar,
.keyboard-adresse,
.keyboard-president-adresse {
  border: 2px solid #ABC270;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(171, 194, 112, 0.3);
  background: white;
  z-index: 1000;
}

/* Input Group Styles */
.input-group-text {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 2px solid #e9ecef;
  border-left: none;
  border-radius: 0 10px 10px 0;
  font-weight: 600;
  color: #495057;
}

.input-group .modern-input {
  border-right: none;
  border-radius: 10px 0 0 10px;
}

.input-group .modern-input:focus+.input-group-text {
  border-color: #ABC270;
}

/* Form Text Styles */
.form-text {
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.text-muted {
  color: #6c757d !important;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .title-wrapper {
    flex-direction: column;
    text-align: center;
  }

  .modern-card-body {
    padding: 1.5rem 1rem;
  }

  .action-buttons {
    flex-direction: column;
    align-items: center;
  }

  .action-btn {
    width: 100%;
    max-width: 300px;
  }

  .upload-section {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 576px) {
  .modern-header {
    margin: 0 -15px 2rem -15px;
    border-radius: 0;
  }

  .header-content {
    padding: 1rem;
  }

  .title-content h4 {
    font-size: 1.25rem;
  }

  .modern-card {
    margin: 0 -15px 1rem -15px;
    border-radius: 0;
  }
}

/* Animation for form transitions */
.modern-form {
  animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loading states */
.modern-btn.loading {
  position: relative;
  color: transparent;
}

.modern-btn.loading::after {
  content: "";
  position: absolute;
  width: 16px;
  height: 16px;
  top: 50%;
  left: 50%;
  margin-left: -8px;
  margin-top: -8px;
  border: 2px solid #ffffff;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Focus indicators for accessibility */
.modern-input:focus,
.modern-btn:focus {
  outline: none;
}

.modern-input:focus-visible,
.modern-btn:focus-visible {
  outline: 2px solid #ABC270;
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .modern-card {
    border: 2px solid #000;
  }

  .modern-input {
    border-width: 2px;
  }

  .modern-btn {
    border: 2px solid currentColor;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {

  .modern-card,
  .modern-btn,
  .modern-input {
    transition: none;
  }

  .modern-form {
    animation: none;
  }
}
</style>