resource "aws_s3_bucket" "assets" {
  bucket = "cloudhub-assets-1777051277"

  tags = {
    Name = "fayen-assets"
  }
}
