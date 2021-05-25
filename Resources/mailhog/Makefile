MAILHOG_UI_GITHUB_URL=https://github.com/mailhog/MailHog-UI.git
MAILHOG_UI_BRANCH = v1.0.0

MAILHOG_UI_DIRECTORY=MailHog-UI

MailHog-UI:
	rm -fr $(MAILHOG_UI_DIRECTORY)
	git clone --branch=$(MAILHOG_UI_BRANCH) $(MAILHOG_UI_GITHUB_URL) $(MAILHOG_UI_DIRECTORY)
	rm -fr $(MAILHOG_UI_DIRECTORY)/.git
	patch --directory=$(MAILHOG_UI_DIRECTORY) --strip=1 < patches/MailHog-UI@$(MAILHOG_UI_BRANCH).patch

.PHONY: MailHog-UI
