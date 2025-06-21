# Mikhmon-GG

![image](https://github.com/user-attachments/assets/53a5707f-d01f-4297-acab-c793c5c30605)

MikroTik Hotspot Monitor (Mikhmon) is a web-based application (MikroTik API class PHP) to help manage the Mikrotik management system, especially hotspot management.

The image build on Alpine Linux, App Mikhmon from Laksamadi Guko and Dockerfile inspiration from Trafex.

## Supported Architectures

We utilise the docker manifest for multi-platform awareness. Simply pulling 99wpx/mikhmon-gg:latest should retrieve the correct image for your arch, but you can also pull specific arch images via tags.

| Architecture | Available | Tag                |
|--------------|-----------|--------------------|
| x86-64       | ✅         | amd64-<version tag> |
| arm64        | ✅         | arm64-<version tag> |
| armhf        | ✅         | arm32v7-<version tag>|

## Usage

Here is an example snippet to help you get started creating a container.

### docker cli

```bash
docker run -d \
  --name=mikhmon \
  -p 8080:80 \
  --restart unless-stopped \
  99wpx/mikhmon-gg:latest
