import { Router, Request, Response } from 'express';
import { listItems, getItemById, downloadItem, getItemVersions } from '../services/item.service';

const router = Router();

router.get('/', (req: Request, res: Response) => {
  const { type, keyword, category, author_id, min_price, max_price, sort, page, page_size } = req.query;
  const result = listItems({
    type: type as string,
    status: 'approved',
    keyword: keyword as string,
    category: category as string,
    authorId: author_id ? parseInt(author_id as string) : undefined,
    minPrice: min_price ? parseFloat(min_price as string) : undefined,
    maxPrice: max_price ? parseFloat(max_price as string) : undefined,
    sort: sort as string,
    page: page ? parseInt(page as string) : 1,
    pageSize: page_size ? parseInt(page_size as string) : 12,
  });
  res.json({ code: 0, data: { items: result.items, total: result.total } });
});

router.get('/categories', (_req: Request, res: Response) => {
  // Return distinct categories from approved items
  res.json({
    code: 0,
    data: ['支付', '通知', '管理', '界面', '安全', 'SEO', '备份', '监控', '其他']
  });
});

router.get('/:id', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const item = getItemById(id);
  if (!item || (item.status !== 'approved' && (!req.session || req.session.userId !== item.author_id))) {
    res.json({ code: 404, msg: '项目不存在' });
    return;
  }
  res.json({ code: 0, data: item });
});

router.get('/:id/download', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const ip = (req.headers['x-forwarded-for'] as string) || req.socket.remoteAddress || 'unknown';
  const userId = req.session?.userId;
  const result = downloadItem(id, userId, ip);
  if (!result.ok || !result.filePath) {
    res.json({ code: 400, msg: result.msg });
    return;
  }
  res.download(result.filePath, result.fileName || 'download.zip');
});

router.get('/:id/versions', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const versions = getItemVersions(id);
  res.json({ code: 0, data: versions });
});

export default router;
