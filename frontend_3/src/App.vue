<template>
  <div id="app" class="container">
    <div class="row hidden-print">
      <HeaderSection/>
      <SearchSection/>
      <MainSection/>
    </div>

  </div>
</template>

<script>
import HeaderSection from "./components/HeaderSection.vue";
import SearchSection from "./components/SearchSection.vue";
import MainSection from "./components/MainSection.vue";
import mitt from 'mitt';

export default {
  name: 'App',
  components: {
    MainSection,
    SearchSection,
    HeaderSection
  }
}
</script>

<style>
</style>
