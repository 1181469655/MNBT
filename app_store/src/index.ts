import express from 'express';
import session from 'express-session';
import * as path from 'path';
import * as fs from 'fs';
import { initDb } from './db';
import authRoutes from './routes/auth';
import itemRoutes from './routes/items';
import developerRoutes from './routes/developer';
import adminRoutes from './routes/admin';
import uploadRoutes from './routes/upload';

const app = express();
const PORT = process.env.PORT || 3000;

// Body parser
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Session
app.use(session({
  secret: 'mnbt-store-secret-key-2025',
  resave: false,
  saveUninitialized: false,
  cookie: {
    httpOnly: true,
    sameSite: 'lax',
    maxAge: 24 * 60 * 60 * 1000
  }
}));

// Static files - uploads (for images)
app.use('/uploads', express.static(path.join(__dirname, '..', 'uploads')));

// Static files - frontend
app.use(express.static(path.join(__dirname, '..', 'public')));

// API routes
app.use('/api/auth', authRoutes);
app.use('/api/items', itemRoutes);
app.use('/api/developer', developerRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/upload', uploadRoutes);

// SPA fallback - serve index.html for all non-api routes
app.get('*', (req, res) => {
  const url = req.path;
  // Skip API routes
  if (url.startsWith('/api/')) {
    res.status(404).json({ code: 404, msg: 'Not Found' });
    return;
  }
  // If request has a file extension, try to serve it
  if (path.extname(url)) {
    const filePath = path.join(__dirname, '..', 'public', url);
    if (fs.existsSync(filePath)) {
      res.sendFile(filePath);
      return;
    }
  }
  // Fallback to index.html for SPA routing or direct .html pages
  if (url === '/') {
    res.sendFile(path.join(__dirname, '..', 'public', 'index.html'));
    return;
  }
  // Try matching .html file
  const htmlPath = path.join(__dirname, '..', 'public', url.replace(/^\//, ''));
  if (fs.existsSync(htmlPath)) {
    res.sendFile(htmlPath);
    return;
  }
  // Try with .html extension
  if (!path.extname(url)) {
    const htmlFile = path.join(__dirname, '..', 'public', url.replace(/^\//, '') + '.html');
    if (fs.existsSync(htmlFile)) {
      res.sendFile(htmlFile);
      return;
    }
  }
  // 404
  res.status(404).sendFile(path.join(__dirname, '..', 'public', 'index.html'));
});

async function start() {
  try {
    await initDb();
    console.log('Database initialized');

    app.listen(PORT, () => {
      console.log(`MNBT Store running at http://localhost:${PORT}`);
      console.log(`Default admin: admin / admin123`);
    });
  } catch (err) {
    console.error('Failed to start server:', err);
    process.exit(1);
  }
}

start();
