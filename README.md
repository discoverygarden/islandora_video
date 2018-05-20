# Video Solution Pack

## Introduction

A framework for ingesting and retrieving video objects in Islandora. Currently supports videos with the following extensions:
`mp4`, `mov`, `qt`, `m4v`, `avi`, `mkv`, `ogg`. A viewer module, such as
[Islandora Video.js](https://github.com/islandora/islandora_videojs), may be enabled to play compatible videos.

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/discoverygarden/islandora)
* [Tuque](https://github.com/islandora/tuque)
* FFmpeg (if creating derivatives locally) - see below for details.
* [ffmpeg2theora](http://v2v.cc/~j/ffmpeg2theora/) (if creating OGG derivatives)

### Installing FFmpeg and AAC Encoders

FFmpeg is a command-line video-processing library, required to create TN, MP4,
and MKV derivatives. This module has been tested with FFmpeg version 1.1.4. It
can be downloaded [here](http://www.ffmpeg.org/releases/ffmpeg-1.1.4.tar.gz).

To support the creation of MP4 files, FFmpeg needs an [AAC
encoder](https://trac.ffmpeg.org/wiki/Encode/AAC). For legacy reasons, this
module uses `libfaac` by default. **Libfaac is not free for commercial use**.
Alternate encoders (such as `libfdk_aac`) may be set in the configuration
options. However, due to license restrictions, the mentioned encoders are not
bundled with FFmpeg and must be enabled when FFmpeg is compiled from source.

Compilation guides:
* [Ubuntu](https://trac.ffmpeg.org/wiki/CompilationGuide/Ubuntu)
* [CentOS](https://trac.ffmpeg.org/wiki/CompilationGuide/Centos)

Sample compile flags: ` --prefix=/usr/local/stow/ffmpeg-1.1.4 --enable-gpl --enable-version3 --enable-nonfree --enable-postproc --enable-libopencore-amrnb --enable-libopencore-amrwb --enable-libdc1394 --enable-libfaac --enable-libgsm --enable-libmp3lame --enable-libopenjpeg --enable-libschroedinger --enable-libspeex --enable-libtheora --enable-libvorbis --enable-libvpx --enable-libx264 --enable-libxvid --enable-libfdk-aac`

## Installation

Install as
[usual](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

## Configuration

Configure this module, including which (if any) derivatives to create, and
which (if any) viewer to use at Configuration » Islandora » Solution Pack Configuration » Video Solution Pack (admin/config/islandora/solution_pack_config/video).

![Configuration](https://user-images.githubusercontent.com/1943338/36505613-7a3df7a2-172a-11e8-8ad0-0c26859ccebc.png)

## Documentation

Further documentation for this module is available at [our
wiki](https://wiki.duraspace.org/display/ISLANDORA/Video+Solution+Pack).

## Troubleshooting/Issues

Having problems or solved one? Create an issue, check out the Islandora Google
groups.

* [Users](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora)
* [Devs](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora-dev)

or contact [discoverygarden](http://support.discoverygarden.ca).

## Maintainers/Sponsors

Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## Development

If you would like to contribute to this module, please check out the helpful
[Documentation](https://github.com/Islandora/islandora/wiki#wiki-documentation-for-developers),
[Developers](http://islandora.ca/developers) section on Islandora.ca and create
an issue, pull request and or contact
[discoverygarden](http://support.discoverygarden.ca).

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
