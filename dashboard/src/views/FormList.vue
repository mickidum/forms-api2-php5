<template>
  <div v-if="allForms" class="all-forms">
    <h1>All Forms Here</h1>
    <div class="list">
      <router-link
        class="outer-link"
        v-for="form in allForms"
        :key="form.form_id"
        :to="form.form_id"
      >
        <div class="card">
          <div class="inners first">
            <span>id:</span>
            <span>{{ form.form_id }}</span>
          </div>
          <h2>{{ form.title }}</h2>
          <div class="inners second">
            <span>source:</span>
            <a :href="form.source">{{ form.source }}</a>
          </div>
          <div class="inners third">
            <span>last update:</span>
            <span>{{form.last_update}}</span>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: "FormList",
  computed: {
    allForms() {
      return this.$store.getters.getAllForms;
    }
  },
  mounted() {
    this.$store.dispatch("fillForms");
  },
  methods: {}
};
</script>

<style lang="scss" scoped>
.all-forms {
  position: relative;
  max-width: 1600px;
  margin: 0 auto;
  h1 {
    margin: 0.67em 10px;
  }
}
.list {
  position: relative;
  display: flex;
  // align-items: center;
  flex-wrap: wrap;
  a {
    text-decoration: none;
  }
  .outer-link {
    text-decoration: none;
    display: block;
    color: #000;
    margin: 0 10px;
    border: solid 1px silver;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
    max-width: 31%;
    &:hover {
      box-shadow: 0 0 10px silver;
    }
    .inners {
      span,
      a {
        word-break: break-all;
      }
      &.first {
        // font-weight: 500;
        // color: blueviolet;
      }
      &.second {
        span {
          // font-weight: 500;
        }
        a {
          display: inline-block;
          z-index: 100;
          color: #2196f3;
          // font-weight: 500;
          &:hover {
            color: blueviolet;
          }
        }
      }
      &.third {
        margin-top: 10px;
        // color: #333;
        // span:first-child {
        // 	font-weight: 500;
        // }
      }
    }
  }
}

@media screen and (max-width: 760px) {
  .list {
    flex-wrap: wrap;
    .outer-link {
      width: 100%;
      max-width: 100%;
    }
  }
}
</style>	