<template>
  <div>
    <el-collapse @change="handleChange">
      <el-collapse-item title="API Pairs" name="1">
        <el-form ref="formRef" :model="user.api_pairs" label-width="120px" class="demo-dynamic">
          <!-- <el-form-item
            prop="email"
            label="Email"
            :rules="[
              {
                required: true,
                message: 'Please input email address',
                trigger: 'blur',
              },
              {
                type: 'email',
                message: 'Please input correct email address',
                trigger: ['blur', 'change'],
              },
            ]"
          >
            <el-input v-model="dynamicValidateForm.email" />
          </el-form-item> -->
          <el-collapse v-if="user.api_pairs.length > 0" @change="handleChange" style="width:70%; margin:auto">
            <el-collapse-item v-for="(pair, index) in user.api_pairs" :title="pair.name" :name="index">
              <template #title>
                {{ pair.name }}
                <!--                <el-button type="danger"><el-icon><Delete /></el-icon></el-button>-->
              </template>
              <el-form-item
                :label="'Name'"
              >
                <el-input v-model="pair.name" placeholder="Please input name" clearable />
              </el-form-item>
              <el-form-item
                :label="'Key'"
              >
                <el-input v-model="pair.api_key" placeholder="Please input api key" clearable />
              </el-form-item>
              <el-form-item
                :label="'Secret'"
              >
                <el-input type="password" v-model="pair.api_secret" placeholder="Please input api secret" clearable />
              </el-form-item>
              <el-form-item
                :label="'Strategy'"
              >
                <el-select-v2 filterable v-model="pair.strategy_id" placeholder="Please select a strategy" :options="user.strategies" clearable>
                  <template #default="{ item }">
                    <span style="margin-right: 8px">{{ item.label }}</span>
      <!--              <span style="font-size: 13px">-->
      <!--                {{ item.value }}-->
      <!--              </span>-->
                  </template>
                </el-select-v2>
              </el-form-item>
              <div style="display:flex; justify-content:center">
                <el-popconfirm  title="Are you sure to delete this?" @confirm="removePair(pair)">
                  <template #reference>
                    <el-button class="mt-4" type="danger">Delete</el-button>
                  </template>
                </el-popconfirm>
              </div>
            </el-collapse-item>
          </el-collapse>
          <el-form-item style="margin:2rem 0 0 2rem">
            <el-button type="primary" @click="updatePairs" :disabled="user?.api_pairs?.length < 1">Submit</el-button>
            <el-button type="success" @click="addPair">New API Pair</el-button>
            <el-button type="warning" @click="showUrl">Show WebHook URL</el-button>
            <!-- <el-button @click="resetForm(formRef)">Reset</el-button> -->
          </el-form-item>
        </el-form>
        <el-alert v-if="error !== null" :title="error" type="error" center show-icon />
      </el-collapse-item>
      <el-collapse-item title="Strategies" name="2">
        <el-form ref="formRef" :model="user.strategies" label-width="120px" class="demo-dynamic">
          <el-collapse v-if="user.strategies.length > 0" style="width:70%; margin:auto">
            <el-collapse-item  v-for="(strategy, index) in user.strategies" :title="strategy.name" :name="strategy.id">
              <template #title>
                {{ strategy.name }}
<!--                <el-button type="danger"><el-icon><Delete /></el-icon></el-button>-->
              </template>
            <el-form-item
              :label="'Name'"
            >
              <el-input v-model="strategy.name" placeholder="Please input name" clearable />
            </el-form-item>
            <el-form-item
              :label="'Code'"
            >
              <el-input v-model="strategy.code" placeholder="Code will be generated whenever you submit" disabled />
            </el-form-item>
            <div style="display:flex; justify-content:center">
              <el-popconfirm  title="Are you sure to delete this?" @confirm="removeStrategy(strategy)">
                <template #reference>
                  <el-button class="mt-4" type="danger">Delete</el-button>
                </template>
              </el-popconfirm>
            </div>
            </el-collapse-item>
          </el-collapse>
          <el-form-item style="margin:2rem 0 0 2rem">
            <el-button type="primary" @click="updateStrategies" :disabled="user?.strategies?.length < 1">Submit</el-button>
            <el-button type="success" @click="addStrategy">New Strategy</el-button>
            <!-- <el-button @click="resetForm(formRef)">Reset</el-button> -->
          </el-form-item>
        </el-form>
        <el-alert v-if="strategyerror !== null" :title="strategyerror" type="error" center show-icon />
      </el-collapse-item>

    </el-collapse>
    </div>
    <!-- <div>
      <Head :title="`${form.first_name} ${form.last_name}`" />
      <div class="flex justify-start mb-8 max-w-3xl">
        <h1 class="text-3xl font-bold">
          <Link class="text-indigo-400 hover:text-indigo-600" href="/users">Users</Link>
          <span class="text-indigo-400 font-medium">/</span>
          {{ form.first_name }} {{ form.last_name }}
        </h1>
        <img v-if="user.photo" class="block ml-4 w-8 h-8 rounded-full" :src="user.photo" />
      </div>
      <trashed-message v-if="user.deleted_at" class="mb-6" @restore="restore"> This user has been deleted. </trashed-message>
      <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
        <form @submit.prevent="update">
          <div class="flex flex-wrap -mb-8 -mr-6 p-8">
            <text-input v-model="form.first_name" :error="form.errors.first_name" class="pb-8 pr-6 w-full lg:w-1/2" label="First name" />
            <text-input v-model="form.last_name" :error="form.errors.last_name" class="pb-8 pr-6 w-full lg:w-1/2" label="Last name" />
            <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/2" label="Email" />
            <text-input v-model="form.password" :error="form.errors.password" class="pb-8 pr-6 w-full lg:w-1/2" type="password" autocomplete="new-password" label="Password" />
            <select-input v-model="form.owner" :error="form.errors.owner" class="pb-8 pr-6 w-full lg:w-1/2" label="Owner">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select-input>
            <file-input v-model="form.photo" :error="form.errors.photo" class="pb-8 pr-6 w-full lg:w-1/2" type="file" accept="image/*" label="Photo" />
          </div>
          <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
            <button v-if="!user.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">Delete User</button>
            <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">Update User</loading-button>
          </div>
        </form>
      </div> -->
</template>

<script>
// import { Head, Link } from '@inertiajs/inertia-vue3'
import Layout from "@/Shared/Layout";
import { Delete } from '@element-plus/icons-vue'
import { ElMessageBox } from 'element-plus'
// import TextInput from '@/Shared/TextInput'
// import FileInput from '@/Shared/FileInput'
// import SelectInput from '@/Shared/SelectInput'
// import LoadingButton from '@/Shared/LoadingButton'
// import TrashedMessage from '@/Shared/TrashedMessage'

export default {
  components: {
    ElMessageBox,
    Delete
    // FileInput,
    // Head,
    // Link,
    // LoadingButton,
    // SelectInput,
    // TextInput,
    // TrashedMessage,
  },
  layout: Layout,
  props: {
    user: Object,
  },
  remember: "form",
  data() {
    return {
      form: this.$inertia.form({
        _method: "put",
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        password: "",
        owner: this.user.owner,
        photo: null,
      }),
      error: null,
      strategyerror: null,
      // sections: {
      //   pairs: false,
      //   strategies: false
      // }
    };
  },
  computed: {
      strategies() {
        return this.user.strategies.map((element) => {
          element.label = element.name
          element.value = element.id
        })
      }
  },
  methods: {
    handleChange(section) {
        // this.sections[section] = !this.sections[section]
    },
    addPair() {
      this.error = null;
      // if(this.user.api_pairs.length > 0) {
      //   this.error = 'Currently you can only add 1 pair';
      //   return
      // }
      // Generate random string to be used for key
      let rand = (Math.random() + 1).toString(36).substring(7);
      while(this.user.api_pairs.find(obj => obj.ind === rand) !== undefined) {
        rand = (Math.random() + 1).toString(36).substring(7);
      }
      this.user.api_pairs.push({
        ind: (Math.random() + 1).toString(36).substring(7),
        name: "Example",
        api_key: "",
        api_secret: "",
        strategy_id: ""
      });
    },
    removePair(item) {
      const index = this.user.api_pairs.indexOf(item);
      if (index !== -1) {
        this.user.api_pairs.splice(index, 1);
        if(item.id !== undefined && item.id !== null && item.id.length !== 0) {
          this.$inertia.delete(`/api-pairs/${item.id}`);
        }
      }
    },
    updateStrategies() {
      // Clear past errors
      this.strategyerror = null;

      // Condition for empty name,key or secret
      const isEmpty = (element) => element.name.length === 0;

      // Validate
      if(this.user.strategies.some(isEmpty)) {
        this.strategyerror = 'Make sure all fields on all pairs are populated !';
      }
      else {
        this.strategyerror = null;

        this.$inertia.post(`/strategies/${this.user.id}`, {
          'strategies': this.user.strategies
        });
      }

    },
    addStrategy() {
      this.strategyerror = null;
      // if(this.user.api_pairs.length > 0) {
      //   this.error = 'Currently you can only add 1 pair';
      //   return
      // }
      // Generate random string to be used for key
      let rand = (Math.random() + 1).toString(36).substring(7);
      while(this.user.strategies.find(obj => obj.ind === rand) !== undefined) {
        rand = (Math.random() + 1).toString(36).substring(7);
      }
      this.user.strategies.push({
        ind: (Math.random() + 1).toString(36).substring(7),
        name: "Example"
      });
    },
    removeStrategy(item) {
      const index = this.user.strategies.indexOf(item);
      if (index !== -1) {
        this.user.strategies.splice(index, 1);
        if(item.id !== undefined && item.id !== null && item.id.length !== 0) {
          this.$inertia.delete(`/strategies/${item.id}`);
        }

      }
    },
    updatePairs() {
      // Clear past errors
      this.error = null;

      // Condition for empty name,key or secret
      const isEmpty = (element) => element.name.length === 0 || element.api_key.length === 0 || element.api_secret.length === 0 || element.strategy_id.length;

      // Validate
      if(this.user.api_pairs.some(isEmpty)) {
        this.error = 'Make sure all fields on all pairs are populated !';
      }
      else {
        this.error = null;

        this.$inertia.post(`/api-pairs/${this.user.id}`, {
          'pairs': this.user.api_pairs
        });
      }

    },
    showUrl() {
      ElMessageBox.alert(this.user.webhookurl, 'Your webhook URL is:', {
        confirmButtonText: "Thanks"
      })
    },
    restore() {
      if (confirm("Are you sure you want to restore this user?")) {
        this.$inertia.put(`/users/${this.user.id}/restore`);
      }
    },
  },
};
</script>
