import AdmZip from 'adm-zip';

interface ZipValidateResult {
  valid: boolean;
  error?: string;
  slug?: string;
}

function validateZip(filePath: string): ZipValidateResult {
  let zip: AdmZip;
  try {
    zip = new AdmZip(filePath);
  } catch {
    return { valid: false, error: '无法解析 zip 文件' };
  }

  const entries = zip.getEntries();
  if (entries.length === 0) {
    return { valid: false, error: 'zip 文件为空' };
  }
  if (entries.length > 500) {
    return { valid: false, error: 'zip 包含文件过多（上限 500）' };
  }

  const rootDirs = new Set<string>();
  let totalSize = 0;

  for (const entry of entries) {
    const name = entry.entryName;

    if (name.includes('..')) {
      return { valid: false, error: '不允许路径穿越' };
    }
    if (name.startsWith('/') || name.startsWith('\\')) {
      return { valid: false, error: '不允许绝对路径' };
    }

    const dataSize = entry.getData().length;
    totalSize += dataSize;
    if (dataSize > 10 * 1024 * 1024) {
      return { valid: false, error: '单个文件不能超过 10MB' };
    }

    const parts = name.split('/');
    if (parts.length > 0 && parts[0]) {
      rootDirs.add(parts[0]);
    }
  }

  // Anti zip-bomb: uncompressed total should not exceed 100MB
  if (totalSize > 100 * 1024 * 1024) {
    return { valid: false, error: 'zip 解压后总大小不能超过 100MB' };
  }

  if (rootDirs.size !== 1) {
    return { valid: false, error: 'zip 根目录必须有且仅有一个文件夹' };
  }

  const slug = Array.from(rootDirs)[0];
  if (!/^[a-zA-Z0-9_\-]{1,63}$/.test(slug)) {
    return { valid: false, error: '文件夹名无效（仅允许字母数字下划线横线，最长63字符）' };
  }

  return { valid: true, slug };
}

export { validateZip };
