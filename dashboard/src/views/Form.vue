<template>
  <div v-if="form" class="cont">
    <!-- <pre>{{form}}</pre> -->
    <div class="single-form">
      <div class="form-menu">
        <span v-if="settings" @click="editSettings(settings)" class="settings">Settings</span>
        <!-- <span class="settings button-csv">Export csv</span> -->
        <downloadexcel
          class="settings button-csv"
          :data="items"
          type="csv"
          :name="form.form_id + '_csv-file.csv'"
        >Export csv</downloadexcel>
        <span @click="deleteForm" class="settings button-delete">Delete form</span>
      </div>
      <header class="single-form-header">
        <h1>{{ form.form_name }}</h1>
        <button
          v-if="checkedRows.length"
          class="button-xsmall pure-button button-error"
          @click="removeChecked"
        >Delete Row</button>
      </header>

      <div class="scrollable">
        <table class="pure-table single-form-table pure-table-bordered">
          <thead v-if="itemsNames">
            <tr>
              <th class="check">
                <input v-model="selectAll" @change="checkAll" type="checkbox" :value="items" />
              </th>
              <th>#</th>
              <th v-for="item in itemsNames" :key="item.name">{{item.title}}</th>
            </tr>
          </thead>
          <tbody v-if="items">
            <tr v-for="(item, index) in items" :key="index">
              <td class="check">
                <input v-model="checkedRows" :value="item" type="checkbox" />
              </td>
              <td>
                <div class="edit">
                  <span>{{index + 1}}</span>
                  <button
                    class="pure-button button-primary button-xsmall button-secondary"
                    @click="editItem(item, index)"
                  >edit</button>
                </div>
              </td>
              <td v-for="(i, index) in item" :key="index">{{ !ifArray(i) ? i : i.join(', ') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="editableItem" class="edit-modal">
      <modal-edit
        :editableItem="editableItem"
        :itemsTitles="itemsNames"
        @closeModal="closeModal"
        @saveItem="saveItem(editableItem)"
      ></modal-edit>
    </div>

    <div v-if="settingsItem" class="edit-modal settilngs-modal">
      <modal-settings
        :settingsItem="settingsItem"
        @closeModal="closeModal"
        @saveSettings="saveSettings"
      ></modal-settings>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import ModalEdit from "../components/ModalEdit";
import ModalSettings from "../components/ModalSettings";
import downloadexcel from "../components/JsonTocsv";
export default {
  components: {
    ModalEdit,
    ModalSettings,
    downloadexcel
  },
  data() {
    return {
      checkedRows: [],
      selectAll: false,
      editableItem: null,
      // editableIndex: null,
      settingsItem: null
    };
  },
  computed: {
    form() {
      return this.$store.getters.getCurrentForm;
    },
    itemsNames() {
      return this.form.settings.items_names;
    },
    items() {
      return this.form.items;
    },
    settings() {
      return this.form.settings;
    }
  },
  methods: {
    ifArray(arr) {
      if (Array.isArray(arr)) {
        return true;
      }
      return false;
    },
    checkAll() {
      this.selectAll
        ? (this.checkedRows = this.items)
        : (this.checkedRows = []);
    },
    removeChecked() {
      let del = confirm("Delete Checked Rows?");
      if (!del) {
        return;
      }
      let items = _.difference(this.items, this.checkedRows);
      this.updateForm(items);
      this.checkedRows = [];
      this.selectAll = false;
    },
    deleteForm() {
      let del = confirm("Delete This Form?");
      if (!del) {
        return;
      }
      this.$store.dispatch("deleteForm", this.form);
      this.$router.push("/");
    },
    editItem(item, index) {
      this.settingsItem = null;
      window.scrollTo({
        top: 0
      });
      this.editableItem = Object.assign({}, item);
    },
    editSettings(item) {
      this.settingsItem = Object.assign({}, item);
    },
    saveItem(item) {
      let upd = confirm("Save Changes?");
      if (!upd) {
        return;
      }
      const index = _.findIndex(this.items, { item_id: item.item_id });
      // const index = _.findIndex(this.items, item)
      // const index = this.editableIndex
      // console.log(index)
      // return
      const items = this.items;
      items.splice(index, 1, item);
      this.updateForm();
      this.closeModal();
    },
    saveSettings(form) {
      let upd = confirm("Save Settings?");
      if (!upd) {
        return;
      }
      this.updateForm(form);
      this.closeModal();
    },
    flatArray(arr) {
      if (Array.isArray(arr)) {
        return arr.join(", ");
      }
      return arr;
    },
    updateForm(items) {
      const form = this.form;
      form.items = items ? items : this.items;
      this.$store.dispatch("updateCurrentForm", form);
    },
    closeModal() {
      this.editableItem = null;
      this.settingsItem = null;
      this.itemsTitles = null;
    }
  },
  mounted() {
    this.$store.dispatch("fillCurrentForm", this.$route.params.form_id);
  }
};
</script>