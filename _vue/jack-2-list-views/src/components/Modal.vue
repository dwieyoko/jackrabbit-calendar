<template>
  <div :class="customClass" class="rr-modal">
    <div class="rr-modal-container">
      <div id="rr-modal-content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  emits: ['close'],

  props: {
    customClass: {
      required: false,
      default: ''
    }
  },

  mounted(){
    //console.log('OnMounted Modal');
    document.addEventListener("click", this.clickOutside, true)
  },

  beforeUnmount(){
    //console.log('onBeforeUnmount Modal');
    document.removeEventListener("click", this.clickOutside, true)
  },

  methods: {
    clickOutside(e) {
      //console.log('clickoutside')
      const el = document.getElementById('rr-modal-content')
      if (el && !el.contains(e.target)) {
        this.$emit('close')
      }
    }
  }
}
</script>

<style scoped>
.rr-modal {
  background-color: rgba(0,0,0,.8);
  bottom: 0;
  left: 0;
  position: fixed;
  right: 0;
  top: 0;
  height: 100%;
  width: 100%;
  overflow-y: scroll;
  overscroll-behavior: contain;
  z-index: 999999;
}
.rr-modal-container {
  max-width: 480px;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
  margin: 0 auto;
  padding-bottom: env(safe-area-inset-bottom);
  padding-top: 60px;
  position: relative;
  width: 100%;
}
</style>
