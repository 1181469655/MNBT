import { run, getOne, getAll, get } from '../db';
import * as path from 'path';
import * as fs from 'fs';
import { validateZip } from './zip.service';

const UPLOAD_DIR = path.join(__dirname, '..', '..', 'uploads');

export interface Item {
  id: number;
  type: string;
  slug: string;
  name: string;
  version: string;
  author_id: number;
  author_name: string;
  price: number;
  description: string;
  main_image: string;
  screenshots: string;
  zip_path: string;
  zip_size: number;
  downloads: number;
  status: string;
  review_msg: string;
  requires_mnbt: string;
  category: string;
  tags: string;
  homepage: string;
  created_at: string;
  updated_at: string;
}

export interface ItemListParams {
  type?: string;
  status?: string;
  keyword?: string;
  category?: string;
  authorId?: number;
  minPrice?: number;
  maxPrice?: number;
  sort?: string;
  page?: number;
  pageSize?: number;
}

export function listItems(params: ItemListParams): { items: Item[]; total: number } {
  const conditions: string[] = [];
  const values: any[] = [];

  if (params.type) {
    conditions.push("type = ?");
    values.push(params.type);
  }
  if (params.status) {
    conditions.push("status = ?");
    values.push(params.status);
  }
  if (params.keyword) {
    conditions.push("(name LIKE ? OR description LIKE ? OR slug LIKE ?)");
    const kw = `%${params.keyword}%`;
    values.push(kw, kw, kw);
  }
  if (params.category) {
    conditions.push("category = ?");
    values.push(params.category);
  }
  if (params.authorId) {
    conditions.push("author_id = ?");
    values.push(params.authorId);
  }
  if (params.minPrice !== undefined) {
    conditions.push("price >= ?");
    values.push(params.minPrice);
  }
  if (params.maxPrice !== undefined) {
    conditions.push("price <= ?");
    values.push(params.maxPrice);
  }

  const where = conditions.length > 0 ? 'WHERE ' + conditions.join(' AND ') : '';
  const countRow = getOne(`SELECT COUNT(*) as cnt FROM items ${where}`, values);

  let orderBy = 'ORDER BY created_at DESC';
  if (params.sort === 'downloads') orderBy = 'ORDER BY downloads DESC';
  if (params.sort === 'newest') orderBy = 'ORDER BY created_at DESC';
  if (params.sort === 'price') orderBy = 'ORDER BY price ASC';

  const page = params.page || 1;
  const pageSize = params.pageSize || 12;
  const offset = (page - 1) * pageSize;

  const items = getAll(
    `SELECT * FROM items ${where} ${orderBy} LIMIT ? OFFSET ?`,
    [...values, pageSize, offset]
  );

  return { items: items || [], total: countRow?.cnt || 0 };
}

export function getItemById(id: number): Item | undefined {
  return getOne("SELECT * FROM items WHERE id = ?", [id]);
}

export function getItemBySlug(type: string, slug: string): Item | undefined {
  return getOne("SELECT * FROM items WHERE type = ? AND slug = ?", [type, slug]);
}

export function createItem(
  authorId: number,
  authorName: string,
  type: string,
  slug: string,
  name: string,
  version: string,
  zipFileName: string,
  price: number,
  description: string,
  category: string,
  tags: string,
  homepage: string,
  mainImage: string,
  requiresMnbt: string
): { ok: boolean; msg: string; item?: Item } {
  if (!slug || !name || !version) {
    return { ok: false, msg: '请填写标识/名称/版本' };
  }
  if (!/^[a-zA-Z0-9_\-]{1,63}$/.test(slug)) {
    return { ok: false, msg: '标识仅允许字母数字下划线横线，最长63字符' };
  }

  const zipPath = path.join(UPLOAD_DIR, 'packages', zipFileName);
  if (!fs.existsSync(zipPath)) {
    return { ok: false, msg: '上传文件不存在' };
  }

  const zipSize = fs.statSync(zipPath).size;

  // Only structural validation, no content parsing
  const validateResult = validateZip(zipPath);
  if (!validateResult.valid) {
    return { ok: false, msg: validateResult.error || 'zip 校验失败' };
  }

  // Verify declared slug matches zip root directory
  if (validateResult.slug !== slug) {
    return { ok: false, msg: `zip 根目录名 "${validateResult.slug}" 与填写的标识 "${slug}" 不一致，请修改` };
  }

  // Check slug uniqueness
  const existing = getOne("SELECT id FROM items WHERE type = ? AND slug = ?", [type, slug]);
  if (existing) {
    return { ok: false, msg: `${type === 'plugin' ? '插件' : '主题'} "${slug}" 已存在` };
  }

  const now = new Date().toISOString();
  run(
    `INSERT INTO items (type, slug, name, version, author_id, author_name, price, description, main_image, screenshots,
     zip_path, zip_size, downloads, status, review_msg, requires_mnbt, category, tags, homepage, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '[]', ?, ?, 0, 'pending', '', ?, ?, ?, ?, ?, ?)`,
    [type, slug, name, version, authorId, authorName, price, description, mainImage,
     zipFileName, zipSize, requiresMnbt, category, tags, homepage, now, now]
  );

  const item = getOne("SELECT * FROM items WHERE type = ? AND slug = ?", [type, slug]);

  run(
    "INSERT INTO item_versions (item_id, version, zip_path, zip_size, changelog, status, created_at) VALUES (?, ?, ?, ?, '', 'approved', ?)",
    [item.id, version, zipFileName, zipSize, now]
  );

  return { ok: true, msg: '提交成功，等待审核', item };
}

export function updateItemInfo(
  itemId: number,
  authorId: number,
  updates: { price?: number; description?: string; category?: string; tags?: string; homepage?: string; mainImage?: string }
): { ok: boolean; msg: string } {
  const item = getOne("SELECT * FROM items WHERE id = ? AND author_id = ?", [itemId, authorId]);
  if (!item) {
    return { ok: false, msg: '项目不存在或无权操作' };
  }

  const now = new Date().toISOString();
  // Track edit requests for admin review
  const fields: string[] = [];
  if (updates.price !== undefined && updates.price !== item.price) {
    run("INSERT INTO edit_requests (item_id, field, old_value, new_value, status, created_at) VALUES (?, 'price', ?, ?, 'pending', ?)",
      [itemId, String(item.price), String(updates.price), now]);
    fields.push('price');
  }
  if (updates.description !== undefined && updates.description !== item.description) {
    run("INSERT INTO edit_requests (item_id, field, old_value, new_value, status, created_at) VALUES (?, 'description', ?, ?, 'pending', ?)",
      [itemId, item.description, updates.description, now]);
    fields.push('description');
  }
  if (updates.category !== undefined && updates.category !== item.category) {
    run("INSERT INTO edit_requests (item_id, field, old_value, new_value, status, created_at) VALUES (?, 'category', ?, ?, 'pending', ?)",
      [itemId, item.category, updates.category, now]);
    fields.push('category');
  }
  if (updates.tags !== undefined && updates.tags !== item.tags) {
    run("INSERT INTO edit_requests (item_id, field, old_value, new_value, status, created_at) VALUES (?, 'tags', ?, ?, 'pending', ?)",
      [itemId, item.tags, updates.tags, now]);
    fields.push('tags');
  }
  if (updates.homepage !== undefined && updates.homepage !== item.homepage) {
    run("INSERT INTO edit_requests (item_id, field, old_value, new_value, status, created_at) VALUES (?, 'homepage', ?, ?, 'pending', ?)",
      [itemId, item.homepage, updates.homepage, now]);
    fields.push('homepage');
  }
  if (updates.mainImage !== undefined && updates.mainImage !== item.main_image) {
    run("INSERT INTO edit_requests (item_id, field, old_value, new_value, status, created_at) VALUES (?, 'main_image', ?, ?, 'pending', ?)",
      [itemId, item.main_image, updates.mainImage, now]);
    fields.push('main_image');
  }

  if (fields.length > 0) {
    // Mark item as pending for edit review
    run("UPDATE items SET status = 'pending', updated_at = ? WHERE id = ?", [now, itemId]);
    return { ok: true, msg: `已提交 ${fields.length} 项修改，等待管理员审核` };
  }

  return { ok: true, msg: '没有需要修改的内容' };
}

export function downloadItem(itemId: number, userId: number | undefined, ip: string): { ok: boolean; msg: string; filePath?: string; fileName?: string } {
  const item = getOne("SELECT * FROM items WHERE id = ? AND status = 'approved'", [itemId]);
  if (!item) {
    return { ok: false, msg: '项目不存在或未审核' };
  }

  const zipPath = path.join(UPLOAD_DIR, 'packages', item.zip_path);
  if (!fs.existsSync(zipPath)) {
    return { ok: false, msg: '文件不存在' };
  }

  // Increment downloads
  run("UPDATE items SET downloads = downloads + 1 WHERE id = ?", [itemId]);

  // Log download
  const now = new Date().toISOString();
  run("INSERT INTO download_logs (item_id, user_id, ip, created_at) VALUES (?, ?, ?, ?)",
    [itemId, userId || null, ip, now]);

  return {
    ok: true,
    msg: 'ok',
    filePath: zipPath,
    fileName: `${item.slug}-v${item.version}.zip`
  };
}

export function getItemVersions(itemId: number) {
  return getAll("SELECT * FROM item_versions WHERE item_id = ? ORDER BY created_at DESC", [itemId]);
}

export function addItemVersion(itemId: number, authorId: number, zipFileName: string, changelog: string, version: string): { ok: boolean; msg: string } {
  const item = getOne("SELECT * FROM items WHERE id = ? AND author_id = ?", [itemId, authorId]);
  if (!item) {
    return { ok: false, msg: '项目不存在或无权操作' };
  }

  const zipPath = path.join(UPLOAD_DIR, 'packages', zipFileName);
  if (!fs.existsSync(zipPath)) {
    return { ok: false, msg: '上传文件不存在' };
  }
  const zipSize = fs.statSync(zipPath).size;

  const now = new Date().toISOString();
  run(
    "INSERT INTO item_versions (item_id, version, zip_path, zip_size, changelog, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', ?)",
    [itemId, version, zipFileName, zipSize, changelog, now]
  );

  // Update item version to latest
  run("UPDATE items SET version = ?, zip_path = ?, zip_size = ?, updated_at = ? WHERE id = ?",
    [version, zipFileName, zipSize, now, itemId]);

  return { ok: true, msg: '新版本上传成功，等待审核' };
}

// Admin functions

export function approveItem(itemId: number, msg: string): { ok: boolean; msg: string } {
  const item = getOne("SELECT * FROM items WHERE id = ?", [itemId]);
  if (!item) return { ok: false, msg: '项目不存在' };
  const now = new Date().toISOString();
  run("UPDATE items SET status = 'approved', review_msg = ?, updated_at = ? WHERE id = ?", [msg || '', now, itemId]);
  return { ok: true, msg: '已审核通过' };
}

export function rejectItem(itemId: number, msg: string): { ok: boolean; msg: string } {
  const item = getOne("SELECT * FROM items WHERE id = ?", [itemId]);
  if (!item) return { ok: false, msg: '项目不存在' };
  const now = new Date().toISOString();
  run("UPDATE items SET status = 'rejected', review_msg = ?, updated_at = ? WHERE id = ?", [msg || '', now, itemId]);
  return { ok: true, msg: '已驳回' };
}

export function suspendItem(itemId: number, msg: string): { ok: boolean; msg: string } {
  const now = new Date().toISOString();
  run("UPDATE items SET status = 'suspended', review_msg = ?, updated_at = ? WHERE id = ?", [msg || '', now, itemId]);
  return { ok: true, msg: '已下架' };
}

export function deleteItem(itemId: number): { ok: boolean; msg: string } {
  const item = getOne("SELECT * FROM items WHERE id = ?", [itemId]);
  if (!item) return { ok: false, msg: '项目不存在' };
  run("DELETE FROM edit_requests WHERE item_id = ?", [itemId]);
  run("DELETE FROM item_versions WHERE item_id = ?", [itemId]);
  run("DELETE FROM download_logs WHERE item_id = ?", [itemId]);
  run("DELETE FROM items WHERE id = ?", [itemId]);
  return { ok: true, msg: '已删除' };
}

export function getEditRequests(status?: string) {
  if (status) {
    return getAll("SELECT er.*, i.name as item_name, i.slug, i.type FROM edit_requests er JOIN items i ON er.item_id = i.id WHERE er.status = ? ORDER BY er.created_at DESC", [status]);
  }
  return getAll("SELECT er.*, i.name as item_name, i.slug, i.type FROM edit_requests er JOIN items i ON er.item_id = i.id ORDER BY er.created_at DESC");
}

export function approveEditRequest(requestId: number): { ok: boolean; msg: string } {
  const req = getOne("SELECT * FROM edit_requests WHERE id = ? AND status = 'pending'", [requestId]);
  if (!req) return { ok: false, msg: '申请不存在或已处理' };

  const now = new Date().toISOString();

  // Apply the edit
  if (req.field === 'price') {
    run("UPDATE items SET price = ? WHERE id = ?", [parseFloat(req.new_value), req.item_id]);
  } else if (req.field === 'description') {
    run("UPDATE items SET description = ? WHERE id = ?", [req.new_value, req.item_id]);
  } else if (req.field === 'category') {
    run("UPDATE items SET category = ? WHERE id = ?", [req.new_value, req.item_id]);
  } else if (req.field === 'tags') {
    run("UPDATE items SET tags = ? WHERE id = ?", [req.new_value, req.item_id]);
  } else if (req.field === 'homepage') {
    run("UPDATE items SET homepage = ? WHERE id = ?", [req.new_value, req.item_id]);
  } else if (req.field === 'main_image') {
    run("UPDATE items SET main_image = ? WHERE id = ?", [req.new_value, req.item_id]);
  }

  run("UPDATE edit_requests SET status = 'approved' WHERE id = ?", [requestId]);

  // Check if all pending edit requests for this item are handled
  const pendingEdits = getOne("SELECT COUNT(*) as cnt FROM edit_requests WHERE item_id = ? AND status = 'pending'", [req.item_id]);
  if (pendingEdits && pendingEdits.cnt === 0) {
    const item = getOne("SELECT * FROM items WHERE id = ?", [req.item_id]);
    if (item && item.status === 'pending') {
      // Was approved before, restore approved status after edits are done
      run("UPDATE items SET status = 'approved', updated_at = ? WHERE id = ?", [now, req.item_id]);
    }
  }

  return { ok: true, msg: '已批准修改' };
}

export function rejectEditRequest(requestId: number): { ok: boolean; msg: string } {
  const req = getOne("SELECT * FROM edit_requests WHERE id = ? AND status = 'pending'", [requestId]);
  if (!req) return { ok: false, msg: '申请不存在或已处理' };

  const now = new Date().toISOString();
  run("UPDATE edit_requests SET status = 'rejected' WHERE id = ?", [requestId]);

  // Check if all pending edits done
  const pendingEdits = getOne("SELECT COUNT(*) as cnt FROM edit_requests WHERE item_id = ? AND status = 'pending'", [req.item_id]);
  if (pendingEdits && pendingEdits.cnt === 0) {
    const item = getOne("SELECT * FROM items WHERE id = ?", [req.item_id]);
    if (item && item.status === 'pending') {
      // Restore original approved status - all edits handled
      run("UPDATE items SET status = 'approved', updated_at = ? WHERE id = ?", [now, req.item_id]);
    }
  }

  return { ok: true, msg: '已驳回修改' };
}

export function getStats(): any {
  const pluginCount = getOne("SELECT COUNT(*) as cnt FROM items WHERE type = 'plugin' AND status = 'approved'");
  const themeCount = getOne("SELECT COUNT(*) as cnt FROM items WHERE type = 'theme' AND status = 'approved'");
  const pendingCount = getOne("SELECT COUNT(*) as cnt FROM items WHERE status = 'pending'");
  const userCount = getOne("SELECT COUNT(*) as cnt FROM users");
  const totalDownloads = getOne("SELECT SUM(downloads) as cnt FROM items");
  const pendingEdits = getOne("SELECT COUNT(*) as cnt FROM edit_requests WHERE status = 'pending'");

  return {
    plugins: pluginCount?.cnt || 0,
    themes: themeCount?.cnt || 0,
    pending: pendingCount?.cnt || 0,
    users: userCount?.cnt || 0,
    downloads: totalDownloads?.cnt || 0,
    pendingEdits: pendingEdits?.cnt || 0,
  };
}
