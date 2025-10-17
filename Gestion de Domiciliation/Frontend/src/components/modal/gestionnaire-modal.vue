<template>
  <!-- Add User -->
  <div class="modal fade" id="add-gestionnaire">
    <div class="modal-dialog modal-dialog-centered custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 custom-modal-header">
              <div class="page-title">
                <h4>Add Gestionnaire</h4>
              </div>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body custom-modal-body">
              <form @submit.prevent="submitForm">
                <div class="row">
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>User Name</label>
                      <input type="text" class="form-control" v-model="user.name" required />
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Email</label>
                      <input type="email" class="form-control" v-model="user.email" required />
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Role</label>
                      <Multiselect
                        :options="roles"
                        v-model="user.is_admin"
                        label="label"
                        track-by="value"
                        placeholder="Choose"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :searchable="false"
                      />
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Password</label>
                      <div class="pass-group">
                        <input type="password" class="pass-input" v-model="user.password" required />
                        <span class="fas toggle-password fa-eye-slash"></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Confirm Password</label>
                      <div class="pass-group">
                        <input type="password" class="pass-input" v-model="user.passConfirm" required />
                        <span class="fas toggle-password fa-eye-slash"></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Status</label>
                      <Multiselect
                        :options="statuses"
                        v-model="user.status"
                        label="label"
                        track-by="value"
                        placeholder="Choose Status"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :searchable="false"
                      />
                    </div>
                  </div>
                </div>
                <div class="modal-footer-btn">
                  <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-submit">Submit</button>
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
  <div class="modal fade" id="edit-gestionnaire">
    <div class="modal-dialog modal-dialog-centered custom-modal-two">
      <div class="modal-content">
        <div class="page-wrapper-new p-0">
          <div class="content">
            <div class="modal-header border-0 custom-modal-header">
              <div class="page-title">
                <h4>Edit Gestionnaire</h4>
              </div>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body custom-modal-body">
              <form @submit.prevent="submitEditForm">
                <div class="row">
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>User Name</label>
                      <input type="text" class="form-control" v-model="editUser.name" required />
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Email</label>
                      <input type="email" class="form-control" v-model="editUser.email" required />
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Current Role</label>
                      <div class="form-control-plaintext">
                        {{ getRoleLabel(editUser.is_admin) }}
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Current Status</label>
                      <div class="form-control-plaintext">
                        {{ getStatusLabel(editUser.status) }}
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Role</label>
                      <Multiselect
                        :options="roles"
                        v-model="editUser.is_admin"
                        label="label"
                        track-by="value"
                        placeholder="Choose"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :searchable="false"
                      />
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="input-blocks">
                      <label>Status</label>
                      <Multiselect
                        :options="statuses"
                        v-model="editUser.status"
                        label="label"
                        track-by="value"
                        placeholder="Choose Status"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :searchable="false"
                      />
                    </div>
                  </div>
                </div>
                <div class="modal-footer-btn">
                  <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-submit">Submit</button>
                </div>
              </form>
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
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

export default {
  components: { Multiselect },
  data() {
    return {
      user: {
        name: "",
        email: "",
        is_admin: null,
        password: "",
        passConfirm: "",
        status: null,
        avatar: null,
      },
      editUser: {
        id: null,
        name: "",
        email: "",
        is_admin: null,
        status: null,
        avatar: null,
      },
      roles: [
        { label: "Admin", value: 1 },
        { label: "User", value: 0 },
      ],
      statuses: [
        { label: "Active", value: "ACTIVE" },
        { label: "Inactive", value: "INACTIVE" },
      ],
    };
  },
  methods: {
    getRoleLabel(val) {
      if (!val && val !== 0) return "User";
      if (typeof val === "object" && val.label) return val.label;
      if (val === 1 || val === "1") return "Admin";
      return "User";
    },
    getStatusLabel(val) {
      if (!val) return "Active";
      if (typeof val === "object" && val.label) return val.label;
      if (val === "ACTIVE") return "Active";
      if (val === "INACTIVE") return "Inactive";
      return "Active";
    },
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.user.avatar = URL.createObjectURL(file);
      }
    },
    onEditFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.editUser.avatar = URL.createObjectURL(file);
      }
    },
    async submitForm() {
      try {
        const payload = {
          name: this.user.name,
          email: this.user.email,
          is_admin: this.user.is_admin && typeof this.user.is_admin === 'object'
            ? this.user.is_admin.value
            : Number(this.user.is_admin ?? 0),
          password: this.user.password,
          password_confirmation: this.user.passConfirm,
          status: this.user.status && typeof this.user.status === 'object'
            ? this.user.status.value
            : (this.user.status ?? "ACTIVE"),
        };
        await axios.post("/users", payload);
        this.$emit("user-added");
        this.resetUser();
        this.hideModal("#add-user");
      } catch (error) {
        alert(
          error.response?.data?.message ||
          (error.response?.data?.errors && JSON.stringify(error.response.data.errors)) ||
          "Failed to add user."
        );
      }
    },
    async submitEditForm() {
      try {
        const payload = {
          name: this.editUser.name,
          email: this.editUser.email,
          is_admin: this.editUser.is_admin && typeof this.editUser.is_admin === 'object'
            ? this.editUser.is_admin.value
            : Number(this.editUser.is_admin ?? 0),
          status: this.editUser.status && typeof this.editUser.status === 'object'
            ? this.editUser.status.value
            : (this.editUser.status ?? "ACTIVE"),
        };
        await axios.put(`/users/${this.editUser.id}`, payload);
        console.log("User updated successfully:", payload);
        this.$emit("user-edited");
        this.resetEditUser();
        this.hideModal("#edit-user");
      } catch (error) {
        alert(
          error.response?.data?.message ||
          (error.response?.data?.errors && JSON.stringify(error.response.data.errors)) ||
          "Failed to update user."
        );
      }
    },
    resetUser() {
      this.user = {
        name: "",
        email: "",
        is_admin: null,
        password: "",
        passConfirm: "",
        status: null,
        avatar: null,
      };
    },
    resetEditUser() {
      this.editUser = {
        id: null,
        name: "",
        email: "",
        is_admin: null,
        status: null,
        avatar: null,
      };
    },
    openEditModal(user) {
      // Always set to option object for Multiselect
      const roleOption = this.roles.find(r => Number(r.value) === Number(user.is_admin));
      const statusOption = this.statuses.find(s => s.value === user.status);
      this.editUser = {
        ...user,
        is_admin: roleOption || this.roles[1], // default to User
        status: statusOption || this.statuses[0], // default to Active
      };
    },
    hideModal(selector) {
      // Bootstrap 5 modal hide
      const modal = window.bootstrap
        ? window.bootstrap.Modal.getInstance(document.querySelector(selector))
        : null;
      if (modal) {
        modal.hide();
      } else {
        // fallback for Bootstrap 4 or if not using bootstrap js
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
  },
};
</script>