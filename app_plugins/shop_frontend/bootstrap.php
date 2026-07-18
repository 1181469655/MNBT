<?php
/**
 * 售卖前端 - 主入口
 * 接管首页，提供 Vue + Vuetify SPA + REST API
 */
if (!defined('IN_CRONLITE')) { exit; }

$pluginDir = __DIR__;
require_once $pluginDir . '/lib/shop_frontend.php';

/* ============================================================
 *  1) 商店首页接管 — mnbt_plugin_dispatch_home 返回 true 则停止默认跳转
 * ============================================================ */
mnbt_add_action('dispatch_home', function () {
    shop_frontend_serve_spa();
    return true;
}, 100);

/* ============================================================
 *  2) SPA 入口 — GET /shop (也可从首页接管进入)
 * ============================================================ */
mnbt_register_route('GET', '/shop', function () {
    shop_frontend_serve_spa();
});

function shop_frontend_serve_spa() {
    $spaDir = __DIR__ . '/views/spa';
    $distIndex = $spaDir . '/dist/index.html';
    if (is_file($distIndex)) {
        readfile($distIndex);
        exit;
    }
    // 开发/回退：直接输出入口 HTML
    ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(shop_frontend_option('site_title', 'MNBT 主机售卖')) ?></title>
  <link rel="icon" type="image/ico" href="<?= htmlspecialchars(shop_frontend_option('site_favicon', '')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/vuetify@3.7.0/dist/vuetify.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:Roboto,sans-serif;background:#f5f5f5}
    #app{min-height:100vh}
    .v-application__wrap{min-height:100vh}
  </style>
</head>
<body>
<div id="app"></div>
<script src="https://cdn.jsdelivr.net/npm/vue@3.5.0/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@3.7.0/dist/vuetify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue-router@4.4.0/dist/vue-router.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios@1.7.0/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pinia@2.2.0/dist/pinia.iife.prod.js"></script>
<?php shop_frontend_render_inline_spa(); ?>
</body>
</html>
    <?php
    exit;
}

/* ============================================================
 *  3) 内联 SPA 渲染（CDN 模式回退方案）
 * ============================================================ */
function shop_frontend_render_inline_spa() {
    $apiBase = shop_frontend_api_url('');
    $siteOpts = [
        'title'   => shop_frontend_option('site_title', 'MNBT 主机售卖'),
        'logo'    => shop_frontend_option('site_logo', ''),
        'primary' => shop_frontend_option('site_primary', '#1867C0'),
        'accent'  => shop_frontend_option('site_accent', '#FF5722'),
        'hero'    => shop_frontend_option('site_hero', '高性能虚拟主机，即买即用'),
        'footer'  => shop_frontend_option('site_footer', '© 2026 MNBT Hosting. All rights reserved.'),
        'currency'=> '¥',
    ];
    $optsJson = json_encode($siteOpts, JSON_UNESCAPED_UNICODE);
    echo "<script>window.__SHOP__={apiBase:" . json_encode($apiBase) . ",opts:{$optsJson}};</script>\n";

    // SPA 主脚本
    ?>
<script>
const { createApp, ref, computed, onMounted, watch } = Vue;
const { createRouter, createWebHistory } = VueRouter;
const { createPinia, defineStore } = Pinia;

const API = window.__SHOP__.apiBase;
const OPTS = window.__SHOP__.opts;

const api = axios.create({ baseURL: API, timeout: 15000 });
function fmt(yuan){ return '¥'+(Number(yuan)||0).toFixed(2); }
function fmtDate(d){ if(!d)return'-'; try{ return new Date(d).toLocaleString('zh-CN'); }catch(e){return d;} }

const useUser = defineStore('user', {
  state:()=>({user:null,loaded:false}),
  getters:{isLogin:(s)=>!!s.user},
  actions:{
    async fetch(){
      try{ const r=await api.get('user'); this.user=r.data.user; }catch(e){this.user=null;}
      this.loaded=true;
    },
    async login(u,p){
      const r=await api.post('login',{username:u,password:p});
      if(r.data.redirect){ await this.fetch(); return true; }
      throw new Error(r.data.code||'登录失败');
    },
    async register(d){
      const r=await api.post('register',d);
      if(r.data.redirect){ await this.fetch(); return true; }
      throw new Error(r.data.code||'注册失败');
    },
    async logout(){
      await api.post('logout'); this.user=null;
    }
  }
});

/* --- 组件 --- */
const AppHeader = {
  template:`
<v-app-bar app elevation="1" color="white">
  <v-container class="d-flex align-items-center">
    <v-app-bar-title>
      <a href="/" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:8px;">
        <v-avatar v-if="opts.logo" size="32"><v-img :src="opts.logo" cover /></v-avatar>
        <span class="text-h6 font-weight-bold">{{opts.title}}</span>
      </a>
    </v-app-bar-title>
    <v-spacer />
    <template v-for="h in homeLinks.filter(l=>l.show==null||l.show)" :key="h.to">
      <v-btn variant="text" :to="h.to">{{h.label}}</v-btn>
    </template>
    <template v-if="user.isLogin">
      <v-btn variant="text" prepend-icon="mdi-wallet" @click="$router.push('/balance')">余额: {{user.user?.balance||'0.00'}}</v-btn>
      <v-menu>
        <template #activator="{props}">
          <v-btn icon v-bind="props"><v-icon>mdi-account-circle</v-icon></v-btn>
        </template>
        <v-list density="compact">
          <v-list-item prepend-icon="mdi-view-dashboard" to="/dashboard" title="我的主机" />
          <v-list-item prepend-icon="mdi-receipt" to="/orders" title="订单" />
          <v-list-item prepend-icon="mdi-account-edit" to="/profile" title="个人信息" />
          <v-divider />
          <v-list-item prepend-icon="mdi-logout" title="退出" @click="doLogout" />
        </v-list>
      </v-menu>
    </template>
    <template v-else>
      <v-btn variant="outlined" to="/login" class="mr-2">登录</v-btn>
      <v-btn color="primary" to="/register">注册</v-btn>
    </template>
  </v-container>
</v-app-bar>`,
  setup(){
    const user = useUser();
    const router = VueRouter.useRouter();
    const homeLinks = computed(()=>[
      {label:'首页',to:'/'},
      {label:'套餐',to:'/shop'},
      {label:'余额充值',to:'/balance',show:user.isLogin},
    ]);
    async function doLogout(){ await user.logout(); router.push('/'); }
    return { opts:OPTS, user, homeLinks, doLogout };
  }
};

const AppFooter = {
  template:`<v-footer app class="bg-grey-lighten-1"><v-container class="text-center text-caption text-medium-emphasis"><div v-html="opts.footer"></div></v-container></v-footer>`,
  setup(){ return { opts:OPTS }; }
};

const PlanCard = {
  props:['plan'],
  template:`
<v-card hover @click="$router.push('/shop/'+plan.id)" class="cursor-pointer h-100">
  <v-card-item>
    <v-card-title>{{plan.name}} <v-chip size="small" color="primary" variant="tonal" class="ml-2">{{plan.spec_type==1?'CDN':'虚拟主机'}}</v-chip></v-card-title>
    <v-card-text class="text-body-2 text-medium-emphasis">{{plan.description || '高性能虚拟主机'}}</v-card-text>
    <v-divider class="my-2" />
    <div class="text-caption px-4">
      <v-row dense>
        <v-col cols="6"><v-icon size="14">mdi-harddisk</v-icon> {{plan.spec_web}} MB</v-col>
        <v-col cols="6"><v-icon size="14">mdi-database</v-icon> {{plan.spec_sql}} MB</v-col>
        <v-col cols="6"><v-icon size="14">mdi-swap-horizontal</v-icon> {{plan.spec_flow>0?plan.spec_flow+'G':'不限'}}</v-col>
        <v-col cols="6"><v-icon size="14">mdi-web</v-icon> {{plan.spec_domain}} 域名</v-col>
      </v-row>
    </div>
  </v-card-item>
  <v-card-actions class="px-4 pb-3">
    <div>
      <template v-if="plan.price_month_cents>0">
        <span class="text-h6 font-weight-bold text-primary">{{fmt(plan.price_month_cents/100)}}</span>
        <span class="text-caption text-medium-emphasis">/月</span>
      </template>
      <template v-if="plan.price_year_cents>0">
        <span v-if="plan.price_month_cents>0" class="mx-1">|</span>
        <span class="text-body-2 font-weight-bold">{{fmt(plan.price_year_cents/100)}}</span>
        <span class="text-caption text-medium-emphasis">/年</span>
      </template>
    </div>
    <v-spacer />
    <v-btn color="primary" size="small">立即购买</v-btn>
  </v-card-actions>
</v-card>`,
  setup(p){ return { fmt, plan:p.plan }; }
};

/* --- 页面 --- */
const HomePage = {
  template:`
<div>
  <v-parallax :src="'data:image/svg+xml,'+encodeURIComponent('<svg xmlns=\\'http://www.w3.org/2000/svg\\' viewBox=\\'0 0 1440 320\\'><path fill=\\'%231867C0\\' fill-opacity=\\'0.1\\' d=\\'M0,192L48,197C96,203,192,213,288,229C384,245,480,267,576,256C672,245,768,203,864,181C960,160,1056,160,1152,181C1248,203,1344,245,1392,267L1440,288L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z\\'></path></svg>')" height="300">
    <v-container class="fill-height">
      <v-row align="center" justify="center">
        <v-col cols="12" md="8" class="text-center">
          <h1 class="text-h3 font-weight-bold mb-4">{{opts.hero}}</h1>
          <p class="text-h6 text-medium-emphasis mb-6">稳定、快速、安全的虚拟主机服务</p>
          <v-btn color="primary" size="large" to="/shop" class="mr-3">查看套餐</v-btn>
          <v-btn variant="outlined" size="large" to="/register">免费注册</v-btn>
        </v-col>
      </v-row>
    </v-container>
  </v-parallax>
  <v-container class="py-10">
    <h2 class="text-center text-h4 font-weight-bold mb-8">为什么选择我们</h2>
    <v-row>
      <v-col v-for="f in features" :key="f.icon" cols="12" sm="4">
        <v-card variant="flat" class="text-center pa-4">
          <v-icon size="48" color="primary">{{f.icon}}</v-icon>
          <h3 class="text-h6 mt-3">{{f.title}}</h3>
          <p class="text-body-2 text-medium-emphasis">{{f.desc}}</p>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <v-container v-if="plans.length" class="pb-10">
    <h2 class="text-center text-h4 font-weight-bold mb-8">热门套餐</h2>
    <v-row>
      <v-col v-for="p in plans.slice(0,3)" :key="p.id" cols="12" md="4">
        <PlanCard :plan="p" />
      </v-col>
    </v-row>
    <div class="text-center mt-6">
      <v-btn variant="outlined" to="/shop" size="large">查看全部套餐</v-btn>
    </div>
  </v-container>
</div>`,
  components:{PlanCard},
  setup(){
    const plans = ref([]);
    onMounted(async()=>{ try{ const r=await api.get('plans'); plans.value=r.data.plans||[]; }catch(e){} });
    const features = [
      {icon:'mdi-shield-check',title:'99.9% 在线率',desc:'企业级硬件，稳定可靠'},
      {icon:'mdi-flash',title:'快速部署',desc:'支付完成后自动开通主机'},
      {icon:'mdi-headset',title:'技术支持',desc:'专业技术团队在线支持'},
    ];
    return {opts:OPTS,plans,features};
  }
};

const ShopPage = {
  template:`
<div><v-container class="py-8"><h1 class="text-h4 font-weight-bold mb-2">主机套餐</h1><p class="text-body-1 text-medium-emphasis mb-6">选择适合您的套餐</p>
<v-row v-if="plans.length"><v-col v-for="p in plans" :key="p.id" cols="12" md="6" lg="4"><PlanCard :plan="p" /></v-col></v-row>
<v-card v-else class="pa-8 text-center"><p class="text-body-1 text-medium-emphasis">暂无可购买的套餐</p></v-card>
</v-container></div>`,
  components:{PlanCard},
  setup(){const plans=ref([]);onMounted(async()=>{try{const r=await api.get('plans');plans.value=r.data.plans||[];}catch(e){}});return{plans};}
};

const PlanDetail = {
  template:`
<v-container v-if="plan" class="py-8" style="max-width:600px">
  <v-btn variant="text" prepend-icon="mdi-arrow-left" to="/shop" class="mb-4">返回套餐列表</v-btn>
  <v-card>
    <v-card-item>
      <v-card-title class="text-h5">{{plan.name}}</v-card-title>
      <v-card-subtitle>{{plan.description}}</v-card-subtitle>
    </v-card-item>
    <v-card-text>
      <v-list density="compact">
        <v-list-item prepend-icon="mdi-harddisk" title="网页空间" :subtitle="plan.spec_web+' MB'" />
        <v-list-item prepend-icon="mdi-database" title="数据库" :subtitle="plan.spec_sql+' MB'" />
        <v-list-item prepend-icon="mdi-swap-horizontal" title="流量" :subtitle="plan.spec_flow>0?plan.spec_flow+' GB':'不限'" />
        <v-list-item prepend-icon="mdi-web" title="域名绑定" :subtitle="plan.spec_domain+' 个'" />
      </v-list>
      <v-divider class="my-3" />
      <v-radio-group v-model="period" inline>
        <v-radio v-if="plan.price_month_cents>0" label="月付 ¥"+fmt(plan.price_month_cents/100) value="month" />
        <v-radio v-if="plan.price_year_cents>0" label="年付 ¥"+fmt(plan.price_year_cents/100) value="year" />
      </v-radio-group>
      <v-select v-if="nodes.length" v-model="node" :items="nodes" item-title="btdh" item-value="btdh" label="开通节点" variant="outlined" density="compact">
        <template #item="{item}">{{item.btdh}}（{{item.btip}}，{{item.btos==1?'Linux':'Windows'}}）</template>
      </v-select>
      <v-alert v-if="!user.isLogin" type="warning" variant="tonal" class="mt-2">
        请先<a href="/login" style="color:inherit">登录</a>后再购买
      </v-alert>
    </v-card-text>
    <v-card-actions class="pa-4">
      <v-btn color="primary" block size="large" @click="buy" :loading="loading" :disabled="!user.isLogin">确认购买</v-btn>
    </v-card-actions>
  </v-card>
  <v-snackbar v-model="snack" :color="snackColor">{{snackMsg}}</v-snackbar>
</v-container>
<v-container v-else class="py-8 text-center"><p>套餐不存在</p></v-container>`,
  setup(){
    const route=VueRouter.useRoute();const user=useUser();const plan=ref(null);const nodes=ref([]);
    const period=ref('month');const node=ref('');const loading=ref(false);const snack=ref(false);const snackMsg=ref('');const snackColor=ref('success');
    onMounted(async()=>{
      try{const r=await api.get('plans');const plans=r.data.plans||[];plan.value=plans.find(p=>p.id==route.params.id)||null;}catch(e){}
      try{const r=await api.get('nodes');nodes.value=r.data.nodes||[];if(nodes.value.length)node.value=nodes.value[0].btdh;}catch(e){}
    });
    async function buy(){
      loading.value=true;
      try{const r=await api.post('create_order',{plan_id:route.params.id,period:period.value,node:node.value});if(r.data.html){document.open();document.write(r.data.html);document.close();}}catch(e){snackMsg.value='创建订单失败';snackColor.value='error';snack.value=true;}
      loading.value=false;
    }
    return { plan,nodes,period,node,loading,snack,snackMsg,snackColor,buy,user,fmt };
  }
};

const LoginPage = {
  template:`
<v-container style="max-width:400px" class="py-8">
  <v-card><v-card-item><v-card-title class="text-center">登录</v-card-title></v-card-item>
    <v-card-text>
      <v-text-field v-model="username" label="用户名" variant="outlined" density="compact" autocomplete="username" />
      <v-text-field v-model="password" label="密码" type="password" variant="outlined" density="compact" autocomplete="current-password" @keyup.enter="doLogin" />
    </v-card-text>
    <v-card-actions class="pa-4"><v-btn color="primary" block @click="doLogin" :loading="loading">登录</v-btn></v-card-actions>
    <div class="text-center pb-4"><a href="/register" class="text-caption">还没有账号？立即注册</a></div>
  </v-card>
  <v-snackbar v-model="snack" color="error">{{snackMsg}}</v-snackbar>
</v-container>`,
  setup(){
    const user=useUser();const router=VueRouter.useRouter();
    const username=ref('');const password=ref('');const loading=ref(false);const snack=ref(false);const snackMsg=ref('');
    async function doLogin(){
      loading.value=true;
      try{await user.login(username.value,password.value);router.push(user.isLogin?'/dashboard':'/');}catch(e){snackMsg.value=e.message;snack.value=true;}
      loading.value=false;
    }
    return{username,password,loading,snack,snackMsg,doLogin};
  }
};

const RegisterPage = {
  template:`
<v-container style="max-width:400px" class="py-8">
  <v-card><v-card-item><v-card-title class="text-center">注册</v-card-title></v-card-item>
    <v-card-text>
      <v-text-field v-model="username" label="用户名" variant="outlined" density="compact" />
      <v-text-field v-model="password" label="密码" type="password" variant="outlined" density="compact" />
      <v-text-field v-model="password2" label="确认密码" type="password" variant="outlined" density="compact" />
      <v-text-field v-model="email" label="邮箱（选填）" variant="outlined" density="compact" />
      <v-text-field v-model="qq" label="QQ（选填）" variant="outlined" density="compact" />
    </v-card-text>
    <v-card-actions class="pa-4"><v-btn color="primary" block @click="doReg" :loading="loading">注册</v-btn></v-card-actions>
    <div class="text-center pb-4"><a href="/login" class="text-caption">已有账号？立即登录</a></div>
  </v-card>
  <v-snackbar v-model="snack" color="error">{{snackMsg}}</v-snackbar>
</v-container>`,
  setup(){
    const user=useUser();const router=VueRouter.useRouter();
    const username=ref('');const password=ref('');const password2=ref('');const email=ref('');const qq=ref('');
    const loading=ref(false);const snack=ref(false);const snackMsg=ref('');
    async function doReg(){
      if(!username.value||!password.value){snackMsg.value='请填写用户名和密码';snack.value=true;return;}
      if(password.value!==password2.value){snackMsg.value='两次密码不一致';snack.value=true;return;}
      loading.value=true;
      try{await user.register({username:username.value,password:password.value,password2:password2.value,email:email.value,qq:qq.value});router.push('/dashboard');}catch(e){snackMsg.value=e.message;snack.value=true;}
      loading.value=false;
    }
    return{username,password,password2,email,qq,loading,snack,snackMsg,doReg};
  }
};

const DashboardPage = {
  template:`
<v-container class="py-8">
  <h1 class="text-h4 font-weight-bold mb-2">我的主机</h1>
  <v-card v-if="!assets.length" class="pa-8 text-center"><p class="text-body-1 text-medium-emphasis">暂无开通的主机</p><v-btn color="primary" to="/shop">去购买</v-btn></v-card>
  <v-table v-else>
    <thead><tr><th>套餐</th><th>主机账号</th><th>节点</th><th>开通时间</th><th>到期时间</th><th>状态</th></tr></thead>
    <tbody><tr v-for="a in assets" :key="a.id"><td>{{a.plan_name}}</td><td>{{a.host_user||'-'}}</td><td>{{a.ssbt||'-'}}</td><td>{{fmtDate(a.created_at)}}</td><td>{{fmtDate(a.expire_at)}}</td><td><v-chip size="small" :color="a.status==='active'?'success':'error'">{{a.status==='active'?'正常':'已到期'}}</v-chip></td></tr></tbody>
  </v-table>
</v-container>`,
  setup(){
    const assets=ref([]);
    onMounted(async()=>{try{const r=await api.get('assets');assets.value=r.data.assets||[];}catch(e){}});
    return{assets,fmtDate};
  }
};

const OrdersPage = {
  template:`
<v-container class="py-8">
  <h1 class="text-h4 font-weight-bold mb-2">我的订单</h1>
  <v-table v-if="orders.length">
    <thead><tr><th>订单号</th><th>套餐</th><th>周期</th><th>金额</th><th>状态</th><th>时间</th></tr></thead>
    <tbody><tr v-for="o in orders" :key="o.id"><td class="text-caption">{{o.order_no}}</td><td>{{o.plan_name}}</td><td>{{o.period==='year'?'年付':'月付'}}</td><td>{{fmt(o.amount_cents/100)}}</td><td><v-chip size="small" :color="statusColor(o.status)">{{statusLabel(o.status)}}</v-chip></td><td>{{fmtDate(o.created_at)}}</td></tr></tbody>
  </v-table>
  <v-card v-else class="pa-8 text-center"><p class="text-body-1 text-medium-emphasis">暂无订单</p></v-card>
</v-container>`,
  setup(){
    const orders=ref([]);
    onMounted(async()=>{try{const r=await api.get('orders');orders.value=r.data.orders||[];}catch(e){}});
    const sl={pending:'待支付',paid:'已支付',opened:'已开通',failed:'失败',cancelled:'已取消'};
    const sc={pending:'warning',paid:'info',opened:'success',failed:'error',cancelled:'error'};
    function statusLabel(s){return sl[s]||s;}
    function statusColor(s){return sc[s]||'grey';}
    return{orders,fmt,fmtDate,statusLabel,statusColor};
  }
};

const BalancePage = {
  template:`
<v-container class="py-8">
  <v-card>
    <v-card-item><v-card-title>我的余额</v-card-title></v-card-item>
    <v-card-text>
      <div class="text-center my-4"><span class="text-h2 font-weight-bold text-primary">{{fmt(balance/100)}}</span></div>
      <v-btn color="primary" block size="large" @click="showRecharge=!showRecharge">充值</v-btn>
      <v-expand-transition>
        <v-card v-if="showRecharge" variant="outlined" class="mt-4 pa-4">
          <v-text-field v-model="amount" label="充值金额（元）" type="number" min="1" variant="outlined" density="compact" />
          <div class="d-flex gap-2 mb-3"><v-btn v-for="a in [10,50,100,500]" :key="a" size="small" variant="outlined" @click="amount=a">{{a}}元</v-btn></div>
          <v-btn color="primary" block @click="recharge" :loading="reLoading">立即充值</v-btn>
        </v-card>
      </v-expand-transition>
    </v-card-text>
  </v-card>
  <v-card class="mt-6"><v-card-item><v-card-title>交易记录</v-card-title></v-card-item>
    <v-table><thead><tr><th>时间</th><th>类型</th><th>金额</th><th>备注</th></tr></thead>
    <tbody><tr v-for="l in logs" :key="l.id"><td>{{fmtDate(l.created_at)}}</td><td>{{typeLabel(l.type)}}</td><td :class="l.amount>=0?'text-success':'text-error'">{{l.amount>=0?'+':''}}{{fmt(Math.abs(l.amount)/100)}}</td><td>{{l.remark||'-'}}</td></tr></tbody></v-table>
  </v-card>
</v-container>`,
  setup(){
    const balance=ref(0);const logs=ref([]);const amount=ref(10);const showRecharge=ref(false);const reLoading=ref(false);
    async function fetch(){try{const r=await api.get('balance');balance.value=r.data.balance_cents||0;logs.value=r.data.logs||[];}catch(e){}}
    onMounted(fetch);
    async function recharge(){
      reLoading.value=true;
      try{const r=await api.post('recharge',{amount:amount.value});if(r.data.html){document.open();document.write(r.data.html);document.close();}}catch(e){}
      reLoading.value=false;
    }
    const tl={recharge:'充值',consume:'消费',refund:'退款',adjust:'调整'};
    function typeLabel(t){return tl[t]||t;}
    return{balance,logs,amount,showRecharge,reLoading,fetch,recharge,typeLabel,fmt,fmtDate};
  }
};

const ProfilePage = {
  template:`
<v-container style="max-width:500px" class="py-8">
  <v-card><v-card-item><v-card-title>个人信息</v-card-title></v-card-item>
    <v-card-text>
      <v-list density="compact">
        <v-list-item title="用户名" :subtitle="user.user?.username||''" />
        <v-list-item title="注册时间" :subtitle="fmtDate(user.user?.created_at)" />
        <v-list-item title="邮箱" :subtitle="user.user?.email||'未设置'" />
        <v-list-item title="QQ" :subtitle="user.user?.qq||'未设置'" />
      </v-list>
    </v-card-text>
  </v-card>
</v-container>`,
  setup(){const user=useUser();return{user,fmtDate};}
};

/* --- 路由 --- */
const routes=[
  {path:'/',component:HomePage,meta:{public:true}},
  {path:'/shop',component:ShopPage,meta:{public:true}},
  {path:'/shop/:id',component:PlanDetail,meta:{public:true}},
  {path:'/login',component:LoginPage,meta:{guest:true}},
  {path:'/register',component:RegisterPage,meta:{guest:true}},
  {path:'/dashboard',component:DashboardPage},
  {path:'/orders',component:OrdersPage},
  {path:'/balance',component:BalancePage},
  {path:'/profile',component:ProfilePage},
];
const router = createRouter({history:createWebHistory(),routes});
router.beforeEach(async(to,from,next)=>{
  const user=useUser();
  if(!user.loaded) await user.fetch();
  if(to.meta.guest && user.isLogin) return next('/dashboard');
  if(!to.meta.public && !user.isLogin) return next('/login');
  next();
});

/* --- 应用 --- */
const pinia = createPinia();
const app = createApp({
  template:`
<v-app>
  <AppHeader />
  <v-main><router-view /></v-main>
  <AppFooter v-if="!$route.meta.guest" />
</v-app>`,
  components:{AppHeader,AppFooter},
});
app.use(pinia);
app.use(router);
app.use(Vuetify.createVuetify({theme:{defaultTheme:'light'}}));
app.mount('#app');
</script>
<?php
}

/* ============================================================
 *  4) REST API 路由（供 SPA 调用）
 * ============================================================ */
mnbt_register_route('GET', '/shop_api/user', function () {
    $user = shop_frontend_get_current_user();
    if ($user) {
        $bal = shop_frontend_get_user_balance((int)$user['id']);
        $user['balance'] = @($bal['balance_cents'] / 100);
    }
    echo json_encode(['user' => $user], JSON_UNESCAPED_UNICODE);
    exit;
});

mnbt_register_route('GET', '/shop_api/plans', function () {
    $plans = shop_frontend_get_plans();
    echo json_encode(['plans' => $plans], JSON_UNESCAPED_UNICODE);
    exit;
});

mnbt_register_route('GET', '/shop_api/nodes', function () {
    $nodes = shop_frontend_get_hosting_nodes();
    echo json_encode(['nodes' => $nodes], JSON_UNESCAPED_UNICODE);
    exit;
});

mnbt_register_route('GET', '/shop_api/assets', function () {
    $user = shop_frontend_get_current_user();
    if (!$user) { echo json_encode(['code'=>'请登录']); exit; }
    $assets = shop_frontend_get_user_assets((int)$user['id']);
    echo json_encode(['assets' => $assets], JSON_UNESCAPED_UNICODE);
    exit;
});

mnbt_register_route('GET', '/shop_api/orders', function () {
    $user = shop_frontend_get_current_user();
    if (!$user) { echo json_encode(['code'=>'请登录']); exit; }
    $orders = shop_frontend_get_user_orders((int)$user['id']);
    echo json_encode(['orders' => $orders['list']], JSON_UNESCAPED_UNICODE);
    exit;
});

mnbt_register_route('GET', '/shop_api/balance', function () {
    $user = shop_frontend_get_current_user();
    if (!$user) { echo json_encode(['code'=>'请登录']); exit; }
    $bal = shop_frontend_get_user_balance((int)$user['id']);
    $logs = shop_frontend_get_balance_logs((int)$user['id']);
    echo json_encode(['balance_cents' => $bal['balance_cents'], 'logs' => $logs['list']], JSON_UNESCAPED_UNICODE);
    exit;
});

mnbt_register_route('POST', '/shop_api/login', function () {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        echo json_encode(['code' => '请填写用户名和密码']);
        exit;
    }
    global $DB;
    $user = $DB->get_row_prepare("SELECT * FROM MN_plugin_user WHERE username=? LIMIT 1", [$username]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['code' => '用户名或密码错误']);
        exit;
    }
    if ((int)$user['status'] !== 1) {
        echo json_encode(['code' => '账户已被禁用']);
        exit;
    }
    $session = $user['id'] . "\t" . md5($user['id'] . $user['password_hash'] . SYS_KEY);
    $token = authcode($session, 'ENCODE', SYS_KEY);
    setcookie('account_token', $token, time() + 86400 * 7, '/');
    $_COOKIE['account_token'] = $token;
    echo json_encode(['code' => '登录成功', 'redirect' => '/']);
    exit;
});

mnbt_register_route('POST', '/shop_api/register', function () {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');
    $email = trim((string)($_POST['email'] ?? ''));
    $qq = trim((string)($_POST['qq'] ?? ''));
    if ($username === '' || $password === '') { echo json_encode(['code'=>'请填写用户名和密码']); exit; }
    if (mb_strlen($username) < 3 || mb_strlen($username) > 32) { echo json_encode(['code'=>'用户名需 3-32 位']); exit; }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) { echo json_encode(['code'=>'用户名只能包含字母、数字、下划线']); exit; }
    if (strlen($password) < 6) { echo json_encode(['code'=>'密码至少 6 位']); exit; }
    if ($password !== $password2) { echo json_encode(['code'=>'两次密码不一致']); exit; }
    global $DB;
    $exists = $DB->get_row_prepare("SELECT id FROM MN_plugin_user WHERE username=? LIMIT 1", [$username]);
    if ($exists) { echo json_encode(['code'=>'用户名已存在']); exit; }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');
    $DB->query_prepare("INSERT INTO MN_plugin_user (username,password_hash,email,qq,status,created_at,updated_at) VALUES (?,?,?,?,1,?,?)", [$username, $hash, $email, $qq, $now, $now]);
    $uid = $DB->lastInsertId();
    $session = $uid . "\t" . md5($uid . $hash . SYS_KEY);
    $token = authcode($session, 'ENCODE', SYS_KEY);
    setcookie('account_token', $token, time() + 86400 * 7, '/');
    $_COOKIE['account_token'] = $token;
    echo json_encode(['code' => '注册成功', 'redirect' => '/']);
    exit;
});

mnbt_register_route('POST', '/shop_api/logout', function () {
    setcookie('account_token', '', time() - 3600, '/');
    echo json_encode(['code' => '已退出']);
    exit;
});

mnbt_register_route('POST', '/shop_api/create_order', function () {
    $user = shop_frontend_get_current_user();
    if (!$user) { echo json_encode(['code'=>'请登录']); exit; }
    $planId = (int)($_POST['plan_id'] ?? 0);
    $period = $_POST['period'] === 'year' ? 'year' : 'month';
    $node = trim((string)($_POST['node'] ?? ''));
    if ($planId <= 0 || $node === '') { echo json_encode(['code'=>'参数不完整']); exit; }
    global $DB;
    $plan = $DB->get_row_prepare("SELECT * FROM MN_plugin_hosting_plan WHERE id=? AND status=1 LIMIT 1", [$planId]);
    if (!$plan) { echo json_encode(['code'=>'套餐不存在']); exit; }
    $amountCents = $period === 'year' ? (int)$plan['price_year_cents'] : (int)$plan['price_month_cents'];
    if ($amountCents <= 0) { echo json_encode(['code'=>'套餐价格配置错误']); exit; }
    $orderNo = 'SF' . date('YmdHis') . rand(1000, 9999);
    $DB->query_prepare("INSERT INTO MN_plugin_hosting_order (user_id,plan_id,plan_name,period,amount_cents,order_no,node,status,created_at) VALUES (?,?,?,?,?,?,?,'pending',?)", [$user['id'], $planId, $plan['name'], $period, $amountCents, $orderNo, $node, date('Y-m-d H:i:s')]);
    $hostOrderId = $DB->lastInsertId();
    // 创建 MN_dd 支付订单
    $pOrderNo = 'MN' . date('YmdHis') . rand(100, 999);
    $DB->query_prepare("INSERT INTO MN_dd (user_id,order_no,lx,money_cents,cs,created_at,status) VALUES (?,?,?,?,?,?,'pending')", [$user['id'], $pOrderNo, 'hosting', $amountCents, json_encode(['hosting_order_id'=>$hostOrderId]), date('Y-m-d H:i:s')]);
    $mnOrderId = $DB->lastInsertId();
    // 触发支付
    $methods = shop_frontend_get_payment_methods();
    if (!empty($methods)) {
        $firstMethod = reset($methods);
        $payPlugin = $firstMethod['plugin'] ?? '';
        $payMethod = $firstMethod['method'] ?? '';
        if ($payPlugin && $payMethod) {
            // 调用支付插件生成支付 HTML
            $GLOBALS['mnbt_plugin_current'] = $payPlugin;
            $paymentHtml = mnbt_apply_filters('payment_gateway_html', '', [$pOrderNo, $amountCents, $mnOrderId, $payMethod]);
            if ($paymentHtml) {
                echo json_encode(['code'=>'ok','html'=>$paymentHtml]);
                exit;
            }
        }
    }
    echo json_encode(['code'=>'ok','redirect'=>'/orders']);
    exit;
});

mnbt_register_route('POST', '/shop_api/recharge', function () {
    $user = shop_frontend_get_current_user();
    if (!$user) { echo json_encode(['code'=>'请登录']); exit; }
    $amount = max(1, min(50000, (float)($_POST['amount'] ?? 10)));
    $amountCents = (int)round($amount * 100);
    global $DB;
    $orderNo = 'RC' . date('YmdHis') . rand(100, 999);
    $DB->query_prepare("INSERT INTO MN_dd (user_id,order_no,lx,money_cents,cs,created_at,status) VALUES (?,?,?,?,?,?,'pending')", [$user['id'], $orderNo, 'recharge', $amountCents, json_encode(['user_id'=>$user['id']]), date('Y-m-d H:i:s')]);
    $methods = shop_frontend_get_payment_methods();
    if (!empty($methods)) {
        $firstMethod = reset($methods);
        $payPlugin = $firstMethod['plugin'] ?? '';
        $payMethod = $firstMethod['method'] ?? '';
        if ($payPlugin && $payMethod) {
            $GLOBALS['mnbt_plugin_current'] = $payPlugin;
            $paymentHtml = mnbt_apply_filters('payment_gateway_html', '', [$orderNo, $amountCents, $DB->lastInsertId(), $payMethod]);
            if ($paymentHtml) {
                echo json_encode(['code'=>'ok','html'=>$paymentHtml]);
                exit;
            }
        }
    }
    echo json_encode(['code'=>'ok','redirect'=>'/balance']);
    exit;
});

/* ============================================================
 *  5) 管理员页面
 * ============================================================ */
mnbt_register_page('admin', 'settings', 'views/admin/settings.php', '售卖前端设置');

mnbt_register_menu('admin', [
    'title' => '售卖前端',
    'icon'  => 'mdi-storefront',
    'order' => 59,
    'children' => [
        ['title' => '前端设置', 'page' => 'settings', 'icon' => 'mdi-cog', 'multitabs' => true],
    ],
]);

/* ============================================================
 *  6) 管理员 AJAX：保存设置
 * ============================================================ */
mnbt_register_ajax('admin', 'shop_frontend_save_settings', function () {
    mnbt_plugin_require_admin();
    $fields = ['site_title','site_logo','site_primary','site_accent','site_hero','site_footer','site_favicon'];
    foreach ($fields as $f) {
        mnbt_plugin_option_set('shop_frontend', $f, trim((string)($_POST[$f] ?? '')));
    }
    echo json_encode(['code'=>'保存成功'], JSON_UNESCAPED_UNICODE);
    exit;
});
