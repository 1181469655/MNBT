import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'MNBT 文档',
  description: '梦奈宝塔主机系统 — 虚拟主机分销、Docker 容器托管、插件与主题生态',
  lang: 'zh-CN',
  base: '/MNBT/',

  themeConfig: {
    logo: '/logo.png',
    search: {
      provider: 'local',
    },

    nav: [
      { text: '使用指南', link: '/guide/intro' },
      { text: 'API 参考', link: '/api/overview' },
      {
        text: '开发文档',
        items: [
          { text: '主题开发', link: '/development/theme/index' },
          { text: '插件开发', link: '/development/plugin/index' },
        ],
      },
      { text: '集成对接', link: '/integration/idcsmart-hosting' },
      {
        text: '更多',
        items: [
          { text: 'PRD 归档', link: '/prd/docker' },
          { text: '插件商店', link: '/store/index' },
        ],
      },
    ],

    sidebar: {
      '/guide/': [
        { text: '使用指南', items: [
          { text: '项目简介', link: '/guide/intro' },
          { text: '安装部署', link: '/guide/install' },
          { text: '目录结构', link: '/guide/directory' },
          { text: '数据库', link: '/guide/database' },
          { text: '宝塔面板对接', link: '/guide/baota' },
          { text: 'Docker 容器服务', link: '/guide/docker' },
          { text: '监控告警', link: '/guide/monitor' },
          { text: '常见问题', link: '/guide/faq' },
          { text: '安全说明', link: '/guide/security' },
          { text: '更新日志', link: '/guide/changelog' },
        ]},
      ],
      '/api/': [
        { text: 'API 参考', items: [
          { text: '通用约定', link: '/api/overview' },
          { text: '后台管理接口', link: '/api/admin' },
          { text: '用户控制面板接口', link: '/api/user' },
          { text: '外部对接 API', link: '/api/external' },
          { text: '插件对接 API', link: '/api/plugin' },
          { text: '核心工具函数', link: '/api/functions' },
          { text: '数据库表速查', link: '/api/database' },
          { text: 'Docker API', link: '/api/docker' },
          { text: 'Docker 宝塔封装', link: '/api/docker-console' },
          { text: 'Docker 魔方财务对接', link: '/api/docker-mofang' },
        ]},
      ],
      '/development/theme/': [
        { text: '主题开发', items: [
          { text: '主题系统总览', link: '/development/theme/index' },
          { text: '主题开发手册', link: '/development/theme/guide' },
          { text: '视图清单', link: '/development/theme/views' },
          { text: '主题引擎进阶', link: '/development/theme/engine' },
          { text: '主页主题开发', link: '/development/theme/home' },
          { text: 'TDesign 双端主题', link: '/development/theme/tdesign' },
          { text: 'TDesign PHP 对接', link: '/development/theme/tdesign-php' },
          { text: 'TDesign 改造计划', link: '/development/plan/tdesign-user-scope' },
        ]},
      ],
      '/development/plugin/': [
        { text: '插件开发', items: [
          { text: '插件系统总览', link: '/development/plugin/index' },
          { text: '插件开发手册', link: '/development/plugin/guide' },
          { text: '菜单与页面', link: '/development/plugin/menu' },
          { text: '路由系统', link: '/development/plugin/route' },
          { text: '支付插件系统', link: '/development/plugin/payment' },
          { text: '钩子与数据库', link: '/development/plugin/hooks' },
        ]},
        { text: '内置插件', items: [
          { text: '内置插件总览', link: '/development/plugin/builtin/' },
          { text: 'user_info 用户信息', link: '/development/plugin/builtin/user-info' },
          { text: 'balance 余额管理', link: '/development/plugin/builtin/balance' },
          { text: 'hosting_shop 主机售卖', link: '/development/plugin/builtin/hosting-shop' },
          { text: 'epay 易支付', link: '/development/plugin/builtin/epay' },
          { text: 'alipay_official 支付宝', link: '/development/plugin/builtin/alipay-official' },
          { text: 'webhook_notify 通知', link: '/development/plugin/builtin/webhook-notify' },
          { text: 'home_demo 首页示例', link: '/development/plugin/builtin/home-demo' },
          { text: 'hello_demo 基础示例', link: '/development/plugin/builtin/hello-demo' },
          { text: 'shop_frontend 售卖前端', link: '/development/plugin/builtin/shop-frontend' },
        ]},
      ],
      '/integration/': [
        { text: '集成对接', items: [
          { text: '魔方财务 × 虚拟主机', link: '/integration/idcsmart-hosting' },
          { text: '魔方财务 × Docker', link: '/integration/idcsmart-docker' },
          { text: 'MNBT 节点连接器', link: '/integration/node-connector' },
          { text: '域名注册 API 对接', link: '/integration/qmzl-domain' },
        ]},
      ],
      '/prd/': [
        { text: 'PRD 归档', items: [
          { text: 'Docker 集成 PRD', link: '/prd/docker' },
          { text: 'Docker PRD 附录', link: '/prd/docker-appendix' },
          { text: '主页系统 PRD', link: '/prd/home' },
          { text: 'Docker × 魔方财务 PRD', link: '/prd/docker-idcsmart' },
          { text: '虚拟主机 × 魔方财务 PRD', link: '/prd/hosting-idcsmart' },
          { text: 'Docker 测试文档', link: '/prd/docker-test' },
        ]},
      ],
      '/store/': [
        { text: '插件商店', items: [
          { text: '商店概述', link: '/store/index' },
          { text: 'Store API', link: '/store/api' },
        ]},
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/1181469655/MNBT' },
    ],

    footer: {
      message: 'MNBT © 2022-2026 梦奈云 版权所有',
    },
  },
})
