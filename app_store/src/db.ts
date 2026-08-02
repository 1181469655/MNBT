import initSqlJs, { Database as SqlJsDatabase } from 'sql.js';
import * as fs from 'fs';
import * as path from 'path';

const DB_PATH = path.join(__dirname, '..', 'data', 'store.db');

let db: SqlJsDatabase;

function saveDb() {
  const dir = path.dirname(DB_PATH);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
  const data = db.export();
  const buffer = Buffer.from(data);
  fs.writeFileSync(DB_PATH, buffer);
}

async function initDb(): Promise<SqlJsDatabase> {
  const SQL = await initSqlJs({
    locateFile: (file: string) => path.join(__dirname, file)
  });

  if (fs.existsSync(DB_PATH)) {
    const fileBuffer = fs.readFileSync(DB_PATH);
    db = new SQL.Database(fileBuffer);
  } else {
    db = new SQL.Database();
  }

  db.run('PRAGMA journal_mode=WAL');
  db.run('PRAGMA foreign_keys=ON');

  db.run(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE NOT NULL,
      password TEXT NOT NULL,
      email TEXT UNIQUE NOT NULL,
      role TEXT NOT NULL DEFAULT 'developer',
      avatar TEXT DEFAULT '',
      bio TEXT DEFAULT '',
      status TEXT NOT NULL DEFAULT 'active',
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL
    )
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS items (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      type TEXT NOT NULL,
      slug TEXT NOT NULL,
      name TEXT NOT NULL,
      version TEXT NOT NULL,
      author_id INTEGER NOT NULL,
      author_name TEXT NOT NULL,
      price REAL NOT NULL DEFAULT 0,
      description TEXT NOT NULL DEFAULT '',
      main_image TEXT DEFAULT '',
      screenshots TEXT DEFAULT '[]',
      zip_path TEXT NOT NULL,
      zip_size INTEGER NOT NULL DEFAULT 0,
      downloads INTEGER NOT NULL DEFAULT 0,
      status TEXT NOT NULL DEFAULT 'pending',
      review_msg TEXT DEFAULT '',
      requires_mnbt TEXT DEFAULT '',
      category TEXT DEFAULT '',
      tags TEXT DEFAULT '[]',
      homepage TEXT DEFAULT '',
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL,
      UNIQUE(type, slug)
    )
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS item_versions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      item_id INTEGER NOT NULL,
      version TEXT NOT NULL,
      zip_path TEXT NOT NULL,
      zip_size INTEGER NOT NULL DEFAULT 0,
      changelog TEXT DEFAULT '',
      status TEXT NOT NULL DEFAULT 'pending',
      created_at TEXT NOT NULL
    )
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS download_logs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      item_id INTEGER NOT NULL,
      user_id INTEGER,
      ip TEXT NOT NULL,
      created_at TEXT NOT NULL
    )
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS edit_requests (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      item_id INTEGER NOT NULL,
      field TEXT NOT NULL,
      old_value TEXT,
      new_value TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'pending',
      created_at TEXT NOT NULL
    )
  `);

  db.run(`CREATE INDEX IF NOT EXISTS idx_items_type_status ON items(type, status)`);
  db.run(`CREATE INDEX IF NOT EXISTS idx_items_author ON items(author_id)`);
  db.run(`CREATE INDEX IF NOT EXISTS idx_items_downloads ON items(downloads)`);
  db.run(`CREATE INDEX IF NOT EXISTS idx_edit_requests_item ON edit_requests(item_id, status)`);
  db.run(`CREATE INDEX IF NOT EXISTS idx_item_versions_item ON item_versions(item_id)`);
  db.run(`CREATE INDEX IF NOT EXISTS idx_download_logs_item ON download_logs(item_id)`);

  // Default admin user: admin / admin123
  const bcrypt = require('bcryptjs');
  const adminExists = db.exec("SELECT id FROM users WHERE username = 'admin'");
  if (!adminExists.length || !adminExists[0].values.length) {
    const hash = bcrypt.hashSync('admin123', 10);
    const now = new Date().toISOString();
    db.run(
      "INSERT INTO users (username, password, email, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
      ['admin', hash, 'admin@mnbt.local', 'admin', 'active', now, now]
    );
  }

  saveDb();
  return db;
}

function getDb(): SqlJsDatabase {
  if (!db) throw new Error('Database not initialized');
  return db;
}

// Helpers for parameterized queries
function run(sql: string, params: any[] = []): void {
  const d = getDb();
  d.run(sql, params);
  saveDb();
}

function get(sql: string, params: any[] = []): any | undefined {
  const d = getDb();
  const stmt = d.prepare(sql);
  stmt.bind(params);
  if (stmt.step()) {
    const cols = stmt.getColumnNames();
    const vals = stmt.get();
    stmt.free();
    const row: any = {};
    cols.forEach((c: string, i: number) => { row[c] = vals[i]; });
    return row;
  }
  stmt.free();
  return undefined;
}

function getAll(sql: string, params: any[] = []): any[] {
  const d = getDb();
  const stmt = d.prepare(sql);
  stmt.bind(params);
  const cols = stmt.getColumnNames();
  const rows: any[] = [];
  while (stmt.step()) {
    const vals = stmt.get();
    const row: any = {};
    cols.forEach((c: string, i: number) => { row[c] = vals[i]; });
    rows.push(row);
  }
  stmt.free();
  return rows;
}

function getOne(sql: string, params: any[] = []): any {
  const rows = getAll(sql, params);
  return rows.length > 0 ? rows[0] : undefined;
}

function exec(sql: string): void {
  const d = getDb();
  d.run(sql);
  saveDb();
}

export { initDb, getDb, run, get, getAll, getOne, exec, saveDb };
