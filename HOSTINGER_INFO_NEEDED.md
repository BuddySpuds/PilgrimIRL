# 🔑 Hostinger Information Needed for Automated Deployment

Please gather this information from your Hostinger hPanel:

---

## 1. SSH Access (Preferred)

**Location:** hPanel → Advanced → SSH Access

```
SSH Host: ssh.hostinger.com (or your specific host)
SSH Port: 65002 (usually)
SSH Username: u123456789
SSH Password: [Your hosting password]
```

**Test SSH access:**
```bash
ssh u123456789@ssh.hostinger.com -p 65002
```

**If SSH not enabled:**
- Go to hPanel → Advanced → SSH Access
- Enable SSH access
- Note the connection details

---

## 2. FTP Credentials (Fallback if no SSH)

**Location:** hPanel → Files → FTP Accounts

```
FTP Host: ftp.pilgrimirl.com (or IP address)
FTP Port: 21
FTP Username: u123456789 (usually same as SSH)
FTP Password: [Your hosting password]
```

---

## 3. MySQL Database (Create New)

**Location:** hPanel → Databases → MySQL Databases

Click "Create new database":

```
Database Name: u123456789_pilgrimirl (note the full name with prefix)
Database User: u123456789_dbuser (or auto-created)
Database Password: [GENERATE STRONG PASSWORD]
Database Host: localhost
```

**⚠️ SAVE THESE CREDENTIALS - You'll need them!**

---

## 4. Domain Configuration

**Verify:**
- [ ] pilgrimirl.com is added to your hosting
- [ ] DNS is pointing to Hostinger (check hPanel → Domains)
- [ ] SSL is enabled (hPanel → SSL → Free SSL)

---

## 5. File Paths

**Default paths on Hostinger:**
```
Web Root: /home/u123456789/domains/pilgrimirl.com/public_html
Home Directory: /home/u123456789
```

---

## 📝 Fill Out This Template

Copy and fill this out:

```bash
# Hostinger Connection Details
HOSTINGER_SSH_HOST="ssh.hostinger.com"
HOSTINGER_SSH_PORT="65002"
HOSTINGER_SSH_USER="u123456789"
HOSTINGER_SSH_PASS="YOUR_HOSTING_PASSWORD"

# Database Details
HOSTINGER_DB_NAME="u123456789_pilgrimirl"
HOSTINGER_DB_USER="u123456789_dbuser"
HOSTINGER_DB_PASS="YOUR_DATABASE_PASSWORD"
HOSTINGER_DB_HOST="localhost"

# Paths
HOSTINGER_WEB_ROOT="/home/u123456789/domains/pilgrimirl.com/public_html"
HOSTINGER_HOME="/home/u123456789"

# Domain
LIVE_DOMAIN="pilgrimirl.com"
```

---

## 🔐 Security Note

**Never commit credentials to git!**

The deployment script will:
1. Ask for credentials interactively, OR
2. Read from a local `.env` file (git-ignored)

---

## ✅ Checklist

Before running automated deployment:

- [ ] SSH access enabled and tested
- [ ] FTP credentials confirmed (backup method)
- [ ] New MySQL database created
- [ ] Database credentials saved
- [ ] Domain pointing to Hostinger
- [ ] SSL certificate active
- [ ] Web root path confirmed

---

## 🆘 How to Find This Information

### SSH Details:
1. hPanel → Advanced → SSH Access
2. Click "Enable SSH Access" if needed
3. Note: Username, Host, Port

### FTP Details:
1. hPanel → Files → FTP Accounts
2. Should show default FTP account
3. Note: Host, Username, Password

### Database:
1. hPanel → Databases → MySQL Databases
2. Create new database
3. Click on database name to see connection details
4. **Save the password immediately!**

### Web Root:
1. hPanel → Files → File Manager
2. Navigate to: domains/pilgrimirl.com/public_html
3. This is your web root

---

## 🚀 Once You Have This Info

Save it to: `/Users/robertporter/Local Sites/pilgrimirl/.env`

```bash
# DO NOT COMMIT THIS FILE
HOSTINGER_SSH_HOST="ssh.hostinger.com"
HOSTINGER_SSH_PORT="65002"
HOSTINGER_SSH_USER="u123456789"
HOSTINGER_SSH_PASS="your_password_here"
HOSTINGER_DB_NAME="u123456789_pilgrimirl"
HOSTINGER_DB_USER="u123456789_dbuser"
HOSTINGER_DB_PASS="your_db_password_here"
HOSTINGER_DB_HOST="localhost"
HOSTINGER_WEB_ROOT="/home/u123456789/domains/pilgrimirl.com/public_html"
```

Then run:
```bash
./auto-deploy.sh
```

The script will handle everything automatically! 🎉
