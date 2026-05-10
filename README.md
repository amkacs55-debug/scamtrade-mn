# ⚔ ML & PUBG Mobile Account Shop
## PHP + Supabase вебсайт

---

## 📁 Файлын бүтэц

```
mlbb-pubg-shop/
├── config.php              ← Supabase + OpenAI тохиргоо
├── index.php               ← Нүүр хуудас (account жагсаалт)
├── login.php               ← Нэвтрэх
├── register.php            ← Бүртгүүлэх
├── account_detail.php      ← Account дэлгэрэнгүй + захиалга
├── chat.php                ← Хэрэглэгчийн чат (AI автомат хариутай)
├── dashboard_user.php      ← Хэрэглэгчийн захиалгууд
├── dashboard_admin.php     ← Админы хяналтын самбар
├── logout.php              ← Гарах
└── supabase_schema.sql     ← Database schema
```

---

## 🚀 Суурилуулах заавар

### 1. Supabase тохиргоо
1. [supabase.com](https://supabase.com) дээр project үүсгэнэ
2. **SQL Editor** дээр `supabase_schema.sql` файлыг ажиллуулна
3. **Settings → API** дээрээс:
   - `Project URL` → `SUPABASE_URL`
   - `anon/public key` → `SUPABASE_ANON_KEY`
   - `service_role key` → `SUPABASE_SERVICE_KEY`

### 2. OpenAI тохиргоо
1. [platform.openai.com](https://platform.openai.com) дээр API key авна
2. `config.php` дотор `OPENAI_API_KEY` тохируулна

### 3. config.php засах
```php
define('SUPABASE_URL', 'https://YOUR_PROJECT.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJ...');
define('SUPABASE_SERVICE_KEY', 'eyJ...');
define('OPENAI_API_KEY', 'sk-...');
define('SITE_URL', 'https://yourdomain.com');
```

### 4. PHP сервер дээр байршуулах
- PHP 8.0+ шаардлагатай
- `curl` extension идэвхтэй байх ёстой
- Apache/Nginx дээр байршуулна

### 5. Admin нэвтрэх
- Email: `admin@mlbbshop.mn`
- Password: `password` *(нэн даруй өөрчлөх!)*

---

## ✨ Функцүүд

| Хэрэглэгч | Админ |
|-----------|-------|
| Бүртгүүлэх / Нэвтрэх | Нэвтрэх |
| Account харах, хайх | Account нийтлэх |
| Тоглоом, төрлөөр шүүх | Захиалга удирдах |
| Account худалдаж авах | Статус өөрчлөх |
| Өдрөөр түрээслэх | Хэрэглэгчтэй чатлах |
| Захиалгын чат | Банкны данс тохируулах |
| AI автомат хариу авах | — |
| Банкны данс харах | — |

---

## 🔄 Захиалгын урсгал

```
Хэрэглэгч → Account харна → Авах/Түрээслэх сонгоно
→ Захиалга үүснэ → AI автомат хариу ирнэ
→ Банкны данс харна → Гүйлгээ хийнэ
→ Чатаар мэдэгдэнэ → Админ баталгаажуулна
→ Account хүргэгдэнэ
```

---

## 💡 Нэмэлт тохиргоо

### Зургийн upload хийх бол
Cloudinary эсвэл Supabase Storage ашиглана.

### Supabase Row Level Security (RLS)
Production дээр RLS идэвхжүүлэхийг зөвлөж байна:
```sql
ALTER TABLE orders ENABLE ROW LEVEL SECURITY;
-- Policies тохируулна
```
