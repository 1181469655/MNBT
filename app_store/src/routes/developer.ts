import { Router, Request, Response } from 'express';
import { requireDeveloper } from '../middleware/auth';
import { uploadImage, uploadZip } from '../middleware/upload';
import { createItem, updateItemInfo, listItems, getItemById, addItemVersion, getItemVersions } from '../services/item.service';
import sanitizeHtml from 'sanitize-html';

const router = Router();

router.use(requireDeveloper);

router.get('/items', (req: Request, res: Response) => {
  const { type, page, page_size } = req.query;
  const result = listItems({
    authorId: req.session.userId!,
    type: type as string,
    page: page ? parseInt(page as string) : 1,
    pageSize: page_size ? parseInt(page_size as string) : 20,
  });
  res.json({ code: 0, data: { items: result.items, total: result.total } });
});

router.post('/items', uploadZip.single('zipfile'), (req: Request, res: Response) => {
  const { type, slug, name, version, price, description, category, tags, homepage, requires_mnbt } = req.body;

  if (!type || !['plugin', 'theme'].includes(type)) {
    res.json({ code: 400, msg: '请选择提交类型（plugin/theme）' });
    return;
  }
  if (!slug || slug.trim().length === 0) {
    res.json({ code: 400, msg: '请填写标识（slug）' });
    return;
  }
  if (!name || name.trim().length === 0) {
    res.json({ code: 400, msg: '请填写名称' });
    return;
  }
  if (!version || version.trim().length === 0) {
    res.json({ code: 400, msg: '请填写版本号' });
    return;
  }
  if (!req.file) {
    res.json({ code: 400, msg: '请上传 zip 文件' });
    return;
  }
  if (!description || description.trim().length === 0) {
    res.json({ code: 400, msg: '请填写简介' });
    return;
  }

  const sanitizedDescription = sanitizeHtml(description, {
    allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'h1', 'h2', 'h3', 'span', 'div', 'pre', 'code']),
    allowedAttributes: {
      ...sanitizeHtml.defaults.allowedAttributes,
      '*': ['style', 'class', 'id']
    }
  });

  let mainImage = '';
  if (req.body.main_image) {
    mainImage = req.body.main_image;
  }

  const result = createItem(
    req.session.userId!,
    req.session.username!,
    type,
    slug.trim(),
    name.trim(),
    version.trim(),
    req.file.filename,
    parseFloat(price) || 0,
    sanitizedDescription,
    category || '',
    tags || '[]',
    homepage || '',
    mainImage,
    requires_mnbt || ''
  );

  res.json({ code: result.ok ? 0 : 400, msg: result.msg, data: result.item });
});

router.put('/items/:id', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }

  const { price, description, category, tags, homepage, main_image } = req.body;
  let sanitizedDescription = description;
  if (description) {
    sanitizedDescription = sanitizeHtml(description, {
      allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'h1', 'h2', 'h3', 'span', 'div', 'pre', 'code']),
      allowedAttributes: {
        ...sanitizeHtml.defaults.allowedAttributes,
        '*': ['style', 'class', 'id']
      }
    });
  }

  const result = updateItemInfo(id, req.session.userId!, {
    price: price !== undefined ? parseFloat(price) : undefined,
    description: sanitizedDescription,
    category,
    tags,
    homepage,
    mainImage: main_image,
  });

  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.post('/items/:id/versions', uploadZip.single('zipfile'), (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  if (!req.file) {
    res.json({ code: 400, msg: '请上传 zip 文件' });
    return;
  }

  const { version, changelog } = req.body;
  if (!version) {
    res.json({ code: 400, msg: '请填写版本号' });
    return;
  }

  const result = addItemVersion(id, req.session.userId!, req.file.filename, changelog || '', version);
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.get('/items/:id', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const item = getItemById(id);
  if (!item || item.author_id !== req.session.userId) {
    res.json({ code: 404, msg: '项目不存在' });
    return;
  }
  res.json({ code: 0, data: item });
});

router.get('/items/:id/versions', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const item = getItemById(id);
  if (!item || item.author_id !== req.session.userId) {
    res.json({ code: 404, msg: '项目不存在' });
    return;
  }
  const versions = getItemVersions(id);
  res.json({ code: 0, data: versions });
});

export default router;
