
# Animest

Animest is an anime scraper designed to efficiently gather anime data from various streaming sites. It organizes and maintains a comprehensive anime library, providing users with seamless access to the latest streams. Ideal for building your personal collection or staying updated with the latest releases, Animest streamlines the process of managing anime content.

## Source Libraries

- GogoAnime
- Vidstream
- MyAnimeList

## Features

- Scrapes from massive anime libraries
- Scrapes video streams from different servers
- Efficiently saves library to its own databases

## Prerequisites

- PHP v8.3 (extensions: sodium, igbinary, msgpack, redis)
- Node v22.x

## Installation

Using composer

```bash
  composer install
  yarn install && yarn build
```

## API Reference

**Note**: This is subject to change

### Get the index of specific anime

```http
  GET /api/anime/${anime_id}
```

| Query       | Type     | Description                                  |
| :---------- | :------- | :------------------------------------------- |
| `anime_id` | `string` | **Required**. Anime ID (eg; no-game-no-life) |

Response example

```json
{
    "id": "no-game-no-life",
    "episodes": [
        "id": "episode-1",
        "title": "Beginner",
        "duration" : "00:24:00",
        ...
    ]
}
```

#### Get the episode of specific anime

```http
  GET /api/videos/${anime_id}/${episode}
```

| Parameter  | Type     | Description                                   |
| :--------- | :------- | :-------------------------------------------- |
| `anime_id` | `string` | **Required**. Anime ID                        |
| `episode`  | `string` | **Required**. Episode index (eg; episode-1)   |

Response example

```json
{
    "id": "no-game-no-life",
    "sources": [
        "main": {
            "file": "https://cdn.animest.land/29816f6de82f35743f50266687d05f28/ep.1.1712333060.m3u8",
            "type": "hls"
        },
        "backup": {
            "file": "",
            "type": "hls"
        }
    ]
}
```

## Authors

- [@RuriXD](https://github.com/RuriXD)
