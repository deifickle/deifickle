---
title: "Virtual Functions III"
date: 2023-02-12T10:13:10+05:30
description: Enforcing traits
tags: [CPP, virtual function, programming]
---

From the previous [post](/posts/2023-01-26-virtualfunc2/) you would've understood that declaring a **pure virtual** function converts a **class** to an **abstract class**. This opens numerous doors and gives us infinite options for structuring our project. We can get rid of cluttered or messed up code flow and enforce elegance to the code!

In games, we need various systems independent of each other to interact/converge in one or more instances. *GamePhysics*; has nothing to do with rendering, it has to bother about the movement and collision of various objects. Should it interact with *GameRenderer*? Yes, it has to, at least when the game is under development, we should be able to visualize the *bounding box*, the *dir vectors* etc of the objects. Thus, we need these two unrelated systems to interact. Likewise, there is no independent system in a game. This is true for almost all systems. Everything has to interact with everything else. There are very few instances where a system can work in complete isolation.

Due to this requirement, we should enforce certain rules so that the classes we create can adapt themselves when these interactions happen. Let's say we have derived all game characters from a common base class `Actor`. What rule can we enforce on these characters to ensure that all game characters can be rendered?

## Abstract class
We know that abstract classes can enforce a set of rules in our programs. To convert a class to an abstract class, we need only one pure virtual function. Even if 9 of 10 functions are defined, the one pure virtual function converts it to an abstract class. This can be utilized effectively to enforce rules and create traits and policies for various elements in our game. This can make the code super flexible and removes code dependencies across different parts of the game code.

## Traits
These rules that we enforce are called traits. Identifying traits in our system and enforcing them plays a vital role in utilizing this pattern efficiently. Let's consider we want to enforce **render** as a rule to `Actor` class, we create a `Renderable` abstract class and *implement* it in the `Actor`.
```c++
class Renderable {
    virtual void render() = 0;
};

class Actor : public Renderable {
    // since Actor is 'derived' from Renderable, 
    // it has to implement the pure virtual function render
    virtual void update() {
        //do actor update
    }

    virtual void render() override {
        //do actor render
    }
};
```
Consider, we should make sure `Actor` is having a couple of *physics* properties, a collision property. We will create an **abstract class** `Collidable` that will enforce certain rules on these collision objects. 

```c++
class Renderable {
    virtual void render() = 0;
};

class Collidable {
    virtual void onCollision() = 0;
    virtual void onEnterCollision() = 0;
    virtual void onExitCollision() = 0;
}

class Actor : public Renderable, Collidable {
    // since Actor is 'derived' from Renderable, 
    // it has to implement the pure virtual function render
    virtual void update() {
        //do actor update
    }

    virtual void render() override {
        //do actor render
    }
    
    virtual void onCollision() override {
        //do actor on collision
    }

    virtual void onEnterCollision() override {
        //do actor enter collision
    }

    virtual void onExitCollision() override {
        //do actor on exit colliison
    }
};
```
Now, we see that the `Actor` is both a `Renderable` and a `Collidable`. This means, all derived types of the `Actor` can be referred to with these two base types `Renderable` and `Collidable` based on the requirement. Suppose, if we are having multiple systems, we can do this
```c++
class Monster : public Actor {
    /// monster stuff
};

class Player : public Actor {
    /// player stuff
};

void main() {
    std::vector<Actor*> allActors;
    std::vector<Collidable*> allCollidables;
    std::vector<Renderable*> allRenderables;

    Monster* m = new Monster;
    Player* p = new Player;

    allActors.push_back(m);
    allActors.push_back(p);

    allCollidables.push_back(m);
    allCollidables.push_back(p);

    allRenderables.push_back(m);
    allRenderables.push_back(p);
}
```

The advantage of enforcing a rule as a trait is; there is no requirement for a direct dependency between two items. Any type can have any property and not have any other property, most of the time. There is a degree of flexibility here when compared to the traditional base and derived class terminology.

## Referencing using Trait

It may look like we are doing redundant stuff; which is not the case. There is much more stuff that is not an `Actor` but will be `Renderable` and `Collidable`, for example, a `tree` in the environment and the `skybox` are `Renderable` but not actors. A `Weapon` and a `Shield` are not `Actors` but are `Collidable` and `Renderable`. An invisible `TriggerArea` is a `Collidable` but is neither a `Renderable` nor an `Actor`. Likewise, numerous combinations of items and rules can be enforced on various classes. 

```c++
class Monster : public Actor {
    /// monster stuff
};

class Player : public Actor {
    /// player stuff
};

class StaticProps : public Renderable, Collidable {
    /// environment stuff
}

class Tree : public StaticProps, Animatable {
    /// tree stuff
}

class Rock : public StaticProps {
    /// rock stuff
}

class Coin : public Renderable, Collidable, Collectable {
    /// coin stuff
}
```
As it is shown in the example code, `allRenderables` can hold any type that will implement the trait `Renderable`. It doesn't matter if they are derived from the same base class. In this context, they are derived from `Renderable` and hence `Renderable` is the base class. The same rule is applied to a `Collidable`, `Animatable`, `Collectable` etc. The idea is, we can refer to all the types under the trait as a reference to the trait itself. Traits pattern helps us in referencing and managing multiple unrelated types as the same type and processing them as a particular system.

Was this series on [Virtual Functions](/tags/virtual-function/) useful? Have any questions? Did I make any mistakes? Let me know on [Twitter](https://twitter.com/madptr) or [Mastodon](https://mastodon.gamedev.place/@madptr)!