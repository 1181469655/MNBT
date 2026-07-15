import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// 构建产物输出到 ../user/dist，供 PHP 主题入口直接引用
export default defineConfig({
  plugins: [vue()],
  base: './',
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir: resolve(__dirname, '../user/dist'),
    emptyOutDir: true,
    assetsDir: 'assets',
    cssCodeSplit: false,
    // PHP 从 /user/*.php 加载 dist，动态 chunk 相对路径会错；打成单包
    chunkSizeWarningLimit: 2500,
    rollupOptions: {
      input: resolve(__dirname, 'index.html'),
      output: {
        entryFileNames: 'assets/index.js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: (info) => {
          const n = info.name || ''
          if (n.endsWith('.css')) return 'assets/index.css'
          return 'assets/[name][extname]'
        },
        inlineDynamicImports: true,
      },
    },
  },
  server: {
    port: 5173,
    proxy: {
      // 开发时把 ajax 代理到本地 MNBT（按需改 target）
      '/user': {
        target: 'http://127.0.0.1',
        changeOrigin: true,
      },
    },
  },
})
