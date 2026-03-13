resource "aws_s3_bucket" "assets" {
  bucket = "fayen-assets-bucket-123456"

  tags = {
    Name = "fayen-assets"
  }
}
