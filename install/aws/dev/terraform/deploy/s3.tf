resource "aws_s3_bucket" "elb_logs" {
  bucket = "wikijump-elb-logs-${var.environment}"
  acl    = "log-delivery-write"
}

resource "aws_s3_bucket" "wikijump_assets" {
  bucket = "wikijump-assets-${var.environment}"
  acl    = "public-read"

  cors_rule {
    allowed_methods = [
      "GET",
      "POST",
      "HEAD",
      "DELETE",
      "PUT"
    ]
    allowed_origins = ["*"]  # TODO: Tighten up in WJ-783
    allowed_headers = ["*"]
    expose_headers = []
    max_age_seconds = 0
  }
  lifecycle_rule {
    enabled = true
    id = "Expire temporary uploads after 1 day"
    prefix = "livewire-tmp"
    tags = {}

    expiration {
      days = 1
      expired_object_delete_marker = false
    }
  }
}
