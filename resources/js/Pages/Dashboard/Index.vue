<template>
  <div style="height:700px">
    <Head title="Trades" />
    <h1 class="mb-8 text-3xl font-bold">BOT Trades</h1>
    <el-row>
      <el-col :span="2">
        <el-button @click="refreshTrades" type="primary">Refresh</el-button>
      </el-col>
      <el-col :span="4">
        <el-select-v2 filterable v-model="pairNameFilterValue" placeholder="Please select a api pair" :options="apiPairs" clearable>
          <template #default="{ item }">
            <span style="margin-right: 8px">{{ item.name }}</span>
          </template>
        </el-select-v2>
      </el-col>
      <el-col :span="4">
        TOTAL PROFIT:
        <ElTag>{{ totalProfit }}</ElTag>
      </el-col>
    </el-row>
    <el-auto-resizer>
      <template #default="{ height, width }">
        <el-table-v2
          fixed
          :columns="columns"
          :data="showedTrades"
          :width="width"
          :height="height"
          :sort-by="sortState"
          @column-sort="onSort"
        />
      </template>
    </el-auto-resizer>
  </div>
</template>

<script>
import { Head } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import Layout from '@/Shared/Layout'
import {
  TableV2FixedDir,
  ElTooltip,
  ElAutoResizer,
  ElTag,
  ElRow,
  ElCol,
  ElInput,
  TableV2SortOrder
} from 'element-plus'
import { h } from 'vue';

export default {
  components: {
    Head,
    TableV2FixedDir,
    ElTooltip,
    ElAutoResizer,
    ElTag,
    ElRow,
    ElCol,
    ElInput
  },
  data() {
      return {
        sortState: {
          'key': 'open_date',
          'order': TableV2SortOrder.DESC
        },
        popoverRef: {},
        pairNameFilterValue: '',
        columns: [
          {
            key: 'open_date',
            title: 'Open Date',
            dataKey: 'open_date',
            width: 200,
            sortable: true,
            cellRenderer({ cellData: created_at_custom }) {
              return h(<ElTag/>, {
                type: ''
              }, () => created_at_custom)
            }
          },
          {
            key: 'close_date',
            title: 'Close Date',
            dataKey: 'close_date',
            width: 200,
            sortable: true,
            cellRenderer({ cellData: created_at_custom }) {
              return h(<ElTag/>, {
                type: ''
              }, () => created_at_custom)
            }
          },
          {
            key: 'api_pair_name',
            title: 'API Name',
            dataKey: 'api_pair_name',
            width: 200,
            cellRenderer({ cellData: pair_name }) {
              return h(<ElTag/>, {
                type: 'success'
              }, () =>pair_name)
            }
          },
          {
            key: 'symbol',
            title: 'Symbol',
            dataKey: 'symbol',
            width: 150,
            cellRenderer({ cellData: symbol }) {
              return h(<ElTag/>, {
                type: 'info'
              }, () =>symbol)
            }
          },
          {
            key: 'price_buy',
            title: 'Buy Price',
            dataKey: 'price_buy',
            width: 150,
            cellRenderer({ cellData: price_buy }) {
              return h(<ElTag/>, {
                type: 'info'
              }, () =>price_buy.toString() + ' $')
            }
          },
          {
            key: 'price_sell',
            title: 'Sell Price',
            dataKey: 'price_sell',
            width: 150,
            cellRenderer({ cellData: price_sell }) {
              if (price_sell === undefined || price_sell === 0) price_sell = 'NOT YET SOLD'
              else price_sell = price_sell.toString() + ' $';
              return h(<ElTag/>, {
                type: 'info'
              }, () =>price_sell)
            }
          },
          {
            key: 'profit',
            title: 'Profit',
            dataKey: 'profit',
            width: 150,
            cellRenderer({ cellData: profit }) {
              let t = null;
              if(profit < 0) t = 'danger';
              else if(profit === 0) {
                t = 'info'
                profit = '0'
              }
              else t = 'success';
              return h(<ElTag/>, {
                type: t
              }, () => profit.toString() + ' $')
              // return h(<ElTag type=t>{profit}</ElTag>)
            },
          }
        ]
      }
  },
  props: {
    trades: Array,
    pairs: Array
  },
  layout: Layout,
  computed: {
    showedTrades() {
      if(this.trades.length > 0)
        return this.trades.filter(trade => trade.api_pair_name.includes(this.pairNameFilterValue))
      else
        return this.trades
    },
    apiPairs() {
      return this.pairs.map((element) => {
        element.value = element.name
        element.label = element.name
        return element;
      });
    },
    totalProfit() {
      let profit = 0;
      if(this.showedTrades.length > 0) {
        this.showedTrades.forEach((element) => {
          profit += element.profit
        });
    }
      return profit.toFixed(4).toString() + ' $';
    }
  },
  mounted() {
    window.Echo.channel('channel')
      .listen('CustomTradeProcessed', (e) => {
        this.refreshTrades();
      })
  },
  destroyed() {
  },
  methods: {
    onSort(sortState) {
      this.trades.reverse();
      this.sortState = sortState;
    },
    refreshTrades() {
      Inertia.reload({ only: ['trades']})
    }
  }
}
</script>
