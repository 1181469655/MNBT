import axios from 'axios';

const api = axios.create({
  baseURL: '/shop_api/',
  timeout: 15000,
});

export default api;
