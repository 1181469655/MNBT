import { Router, Request, Response } from 'express';
import { requireLogin } from '../middleware/auth';
import { uploadImage as uploadImageMiddleware } from '../middleware/upload';

const router = Router();

router.use(requireLogin);

router.post('/image', uploadImageMiddleware.single('image'), (req: Request, res: Response) => {
  if (!req.file) {
    res.json({ code: 400, msg: '上传失败' });
    return;
  }
  const url = `/uploads/images/${req.file.filename}`;
  res.json({ code: 0, msg: '上传成功', data: { url, filename: req.file.filename } });
});

export default router;
