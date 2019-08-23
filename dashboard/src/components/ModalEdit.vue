<template>
  <div class="inner">
    <div class="close">
      <span @click="closeModal">&times;</span>
    </div>
    <form @submit.prevent="saveItem" class="pure-form pure-form-stacked">
      <div>
        <template v-for="t in itemsTitles">
          <template v-if="t.name === key" v-for="(item, key) in editableItem">
            <label class="input-item" v-if="!ifArray(item)">
              <template v-focus v-if="key !== 'item_id' && key !== 'date'">
                {{t.title}}
                <input v-if="words(item) <= 3" type="text" v-model="editableItem[key]" />
                <textarea v-if="words(item) > 3" v-model="editableItem[key]"></textarea>
              </template>
            </label>
            <div class="is-array input-item" v-else-if="ifArray(item)">
              <p>
                {{t.title}}
                <span
                  class="pure-button button-xsmall button-secondary"
                  @click="addField(key)"
                >add field</span>
              </p>
              <div class="delete-field" v-for="(i, index) in item">
                <input
                  type="text"
                  v-model="editableItem[key][index]"
                  :placeholder="'name of ' + key"
                />
                <span @click="delField(index, key)">-</span>
              </div>
            </div>
          </template>
        </template>
      </div>
      <p>
        <button type="submit" class="pure-button pure-button-primary">Save Item</button>&nbsp;
        <button
          @click.prevent="closeModal"
          class="pure-button button-secondary pure-button-primary"
        >Cancel</button>
      </p>
    </form>
  </div>
</template>

<script>
export default {
  props: ["editableItem", "itemsTitles"],
  mounted() {
    document.body.classList.add("no-scroll");
  },
  methods: {
    closeModal() {
      document.body.classList.remove("no-scroll");
      this.$emit("closeModal");
    },
    saveItem(item) {
      this.clearEmptyArraysFields(this.editableItem);
      this.$emit("saveItem", item);
    },
    clearEmptyArraysFields(obj) {
      let arrays = Object.keys(obj).filter(key => {
        if (this.ifArray(obj[key])) {
          return key;
        }
      });
      if (arrays.length) {
        arrays.forEach(key => {
          this.editableItem[key] = this.editableItem[key].filter(item => {
            item = item.trim();
            return item !== "";
          });
        });
      }
    },
    words(str) {
      let s = str.split(/[\s,]/);
      return s.length;
    },
    ifArray(arr) {
      if (Array.isArray(arr)) {
        return true;
      }
      return false;
    },
    findEmptyFields(arr) {
      let emptyField = arr.filter(item => {
        return item === "";
      });
      if (emptyField.length) {
        return true;
      }
      return false;
    },
    addField(key) {
      this.editableItem[key] = Object.assign([], this.editableItem[key]);
      if (this.findEmptyFields(this.editableItem[key])) {
        return;
      }
      this.editableItem[key].unshift("");
    },
    delField(index, key) {
      this.editableItem[key] = this.editableItem[key].filter((item, i) => {
        return i !== index;
      });
    }
  }
};
</script>