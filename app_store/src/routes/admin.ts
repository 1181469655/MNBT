import { Router, Request, Response } from 'express';
import { requireAdmin } from '../middleware/auth';
import {
  listItems, approveItem, rejectItem, suspendItem, deleteItem,
  getEditRequests, approveEditRequest, rejectEditRequest, getStats, getItemById
} from '../services/item.service';
import { listUsers, updateUserStatus } from '../services/user.service';

const router = Router();

router.use(requireAdmin);

router.get('/stats', (_req: Request, res: Response) => {
  const stats = getStats();
  res.json({ code: 0, data: stats });
});

router.get('/items', (req: Request, res: Response) => {
  const { type, status, keyword, page, page_size } = req.query;
  const result = listItems({
    type: type as string,
    status: status as string,
    keyword: keyword as string,
    page: page ? parseInt(page as string) : 1,
    pageSize: page_size ? parseInt(page_size as string) : 20,
  });
  res.json({ code: 0, data: { items: result.items, total: result.total } });
});

router.put('/items/:id/approve', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const result = approveItem(id, req.body.msg || '');
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.put('/items/:id/reject', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  if (!req.body.msg || req.body.msg.trim() === '') {
    res.json({ code: 400, msg: '请填写驳回原因' });
    return;
  }
  const result = rejectItem(id, req.body.msg);
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.put('/items/:id/suspend', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const result = suspendItem(id, req.body.msg || '已下架');
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.delete('/items/:id', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const result = deleteItem(id);
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.get('/edit-requests', (req: Request, res: Response) => {
  const { status } = req.query;
  const requests = getEditRequests(status as string);
  res.json({ code: 0, data: requests });
});

router.put('/edit-requests/:id/approve', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const result = approveEditRequest(id);
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.put('/edit-requests/:id/reject', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const result = rejectEditRequest(id);
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

router.get('/users', (req: Request, res: Response) => {
  const { page, page_size } = req.query;
  const result = listUsers(
    page ? parseInt(page as string) : 1,
    page_size ? parseInt(page_size as string) : 20
  );
  res.json({ code: 0, data: result });
});

router.put('/users/:id', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    res.json({ code: 400, msg: '无效的 ID' });
    return;
  }
  const { status } = req.body;
  if (!status || !['active', 'banned'].includes(status)) {
    res.json({ code: 400, msg: '无效的状态值' });
    return;
  }
  const result = updateUserStatus(id, status);
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

export default router;
