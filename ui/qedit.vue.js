const Qedit = {
    template: `
        <div>
          <label>{{k}}</label>
          <div v-if="edit">
            <input 
                v-on:keyup.enter="save"
                @keydown.esc="cancel"
                v-focus
               v-model="v" type="text">
            <span @click="cancel">cancel</span>
          </div>
          <div v-else @click="edit=true">{{v}}</div>
        </div>
    `,
    props:{
      name: {type:String},
      value: {type:String}
    },
    data: function(){
        return {
          k: this.name,
          v: this.value,
          edit:false
        }
    },
    methods:{
      cancel: function(){
        this.v = this.value
        this.edit = false
      },
      save: function(){
        console.log("saveing", this.k, this.v)
        this.$emit("changed", {name:this.k, value:this.v})
        this.edit = false
      }
    },
    computed: {
      authstatus() {
        //  used to show/hide `content` link
        return this.$root.authstatus;
      },
    },
    directives: {
      focus: {
        // directive definition
        inserted: function (el) {
          el.focus()
        }
      }
    }
  };
  